<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\controller;

use phpbb\controller\helper;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\user;
use slamb\externallogin\exception\provider_exception;
use slamb\externallogin\provider\identity;
use slamb\externallogin\provider\provider_interface;
use slamb\externallogin\service\account_repository;
use slamb\externallogin\service\provider_manager;
use slamb\externallogin\service\state_storage;
use slamb\externallogin\service\user_creator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Sends the visitor to the identity provider and handles the way back.
 */
class auth
{
	public function __construct(
		protected helper $helper,
		protected language $language,
		protected request_interface $request,
		protected user $user,
		protected provider_manager $providers,
		protected state_storage $states,
		protected account_repository $accounts,
		protected user_creator $creator,
		protected string $root_path,
		protected string $php_ext
	)
	{
	}

	/**
	 * Start a sign-in: remember state, nonce and PKCE verifier, then leave for the provider.
	 */
	public function start(string $provider): Response
	{
		$this->language->add_lang('common', 'slamb/externallogin');

		$entry = $this->get_enabled_provider($provider);

		if ($this->user->data['is_registered'])
		{
			$this->go_to($this->get_default_target());
		}

		$redirect = $this->request->variable('redirect', '');
		if (strlen($redirect) > 255)
		{
			$redirect = '';
		}

		$nonce = bin2hex(random_bytes(16));
		$verifier = $this->base64url(random_bytes(48));
		$challenge = $this->base64url(hash('sha256', $verifier, true));

		$state = $this->states->create_state($provider, (string) $this->user->session_id, $nonce, $verifier, $redirect);

		$url = $entry->get_authorize_url(
			$this->providers->get_client_id($provider),
			$this->providers->get_redirect_uri($provider),
			$state,
			$nonce,
			$challenge
		);

		return new RedirectResponse($url);
	}

	/**
	 * The visitor is back from the provider.
	 */
	public function callback(string $provider): Response
	{
		$this->language->add_lang('common', 'slamb/externallogin');

		$entry = $this->get_enabled_provider($provider);

		if ($this->user->data['is_registered'])
		{
			$this->go_to($this->get_default_target());
		}

		// The state proves that this answer belongs to a sign-in started in this very browser session
		$state = $this->request->variable('state', '');
		$record = $state !== '' ? $this->states->consume_state($state) : null;

		if ($record === null
			|| $record['provider'] !== $provider
			|| !hash_equals((string) $record['session_id'], (string) $this->user->session_id))
		{
			throw new http_exception(Response::HTTP_BAD_REQUEST, 'SLAMB_EXTLOGIN_ERR_STATE');
		}

		$error = $this->request->variable('error', '');
		if ($error !== '')
		{
			throw new http_exception(Response::HTTP_BAD_REQUEST, $error === 'access_denied' ? 'SLAMB_EXTLOGIN_ERR_CANCELLED' : 'SLAMB_EXTLOGIN_ERR_PROVIDER');
		}

		$code = $this->request->variable('code', '');
		if ($code === '')
		{
			throw new http_exception(Response::HTTP_BAD_REQUEST, 'SLAMB_EXTLOGIN_ERR_STATE');
		}

		try
		{
			$identity = $entry->fetch_identity(
				$this->providers->get_client_id($provider),
				$this->providers->get_secret($provider),
				$this->providers->get_redirect_uri($provider),
				$code,
				(string) $record['verifier'],
				(string) $record['nonce']
			);
		}
		catch (provider_exception $exception)
		{
			// The details are for the admin, the visitor only learns that it failed
			error_log('[slamb/externallogin] ' . $provider . ': ' . $exception->getMessage());
			throw new http_exception(Response::HTTP_BAD_GATEWAY, 'SLAMB_EXTLOGIN_ERR_PROVIDER');
		}

		return $this->handle_identity($identity, (string) $record['redirect']);
	}

	/**
	 * Decide what the verified identity means for this board.
	 */
	protected function handle_identity(identity $identity, string $redirect): Response
	{
		// 1. Known account: sign in
		$user_id = $this->accounts->find_user_id($identity->provider, $identity->uid);
		if ($user_id)
		{
			$this->sign_in($user_id, $redirect);
		}

		// 2. A forum account with the same e-mail address
		if ($identity->email !== '')
		{
			$matches = $this->accounts->find_users_by_email($identity->email);

			if ($matches)
			{
				// Only an address the provider vouches for may take over a forum account,
				// and never an account with elevated rights or an ambiguous match
				if ($identity->email_verified && count($matches) === 1 && !$this->accounts->is_privileged($matches[0]))
				{
					$this->accounts->link((int) $matches[0]['user_id'], $identity);
					$this->sign_in((int) $matches[0]['user_id'], $redirect);
				}

				throw new http_exception(Response::HTTP_CONFLICT, 'SLAMB_EXTLOGIN_ERR_EMAIL_EXISTS');
			}
		}

		// 3. A new visitor: registration, if the board allows it
		if (!$this->creator->is_registration_open())
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'UCP_REGISTER_DISABLE');
		}

		if ($this->creator->has_required_profile_fields())
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'SLAMB_EXTLOGIN_ERR_PROFILE_FIELDS');
		}

		$token = $this->states->create_pending($identity, (string) $this->user->session_id, $redirect);

		return new RedirectResponse($this->helper->route('slamb_externallogin_register', ['token' => $token], false, '', UrlGeneratorInterface::ABSOLUTE_URL));
	}

	/**
	 * Sign the user in and leave for the page the visitor came from.
	 */
	protected function sign_in(int $user_id, string $redirect): never
	{
		$user = $this->accounts->get_user($user_id);

		if ($user === null)
		{
			throw new http_exception(Response::HTTP_BAD_GATEWAY, 'SLAMB_EXTLOGIN_ERR_PROVIDER');
		}

		if (in_array((int) $user['user_type'], [USER_INACTIVE, USER_IGNORE], true))
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'ACTIVE_ERROR');
		}

		// Bans are checked in here and stop the request with the usual message
		$this->user->session_create($user_id, false, false, true);

		$this->go_to($redirect !== '' ? $redirect : $this->get_default_target());
	}

	protected function get_enabled_provider(string $provider): provider_interface
	{
		$entry = $this->providers->get_enabled($provider);

		if ($entry === null)
		{
			throw new http_exception(Response::HTTP_NOT_FOUND, 'SLAMB_EXTLOGIN_ERR_DISABLED');
		}

		return $entry;
	}

	protected function get_default_target(): string
	{
		return append_sid($this->root_path . 'index.' . $this->php_ext);
	}

	/**
	 * phpBB's own redirect() only leads to this board and replaces stale session ids.
	 */
	protected function go_to(string $url): never
	{
		redirect(reapply_sid($url));
	}

	protected function base64url(string $binary): string
	{
		return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
	}
}
