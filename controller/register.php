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

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use slamb\externallogin\provider\identity;
use slamb\externallogin\service\account_repository;
use slamb\externallogin\service\logger;
use slamb\externallogin\service\provider_manager;
use slamb\externallogin\service\state_storage;
use slamb\externallogin\service\user_creator;
use Symfony\Component\HttpFoundation\Response;

/**
 * The last step for a new visitor: pick a username and accept the terms.
 */
class register
{
	protected const FORM_KEY = 'slamb_externallogin_register';

	public function __construct(
		protected config $config,
		protected helper $helper,
		protected language $language,
		protected request_interface $request,
		protected template $template,
		protected user $user,
		protected provider_manager $providers,
		protected state_storage $states,
		protected account_repository $accounts,
		protected user_creator $creator,
		protected string $root_path,
		protected string $php_ext,
		protected logger $logger
	)
	{
	}

	public function handle(string $token): Response
	{
		include_once $this->root_path . 'includes/functions_user.' . $this->php_ext;

		$this->language->add_lang('common', 'slamb/externallogin');
		$this->language->add_lang('ucp');

		if ($this->user->data['is_registered'])
		{
			redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
		}

		$pending = $this->states->get_pending($token, (string) $this->user->session_id);
		$provider = $pending !== null ? ($this->providers->all()[$pending['provider']] ?? null) : null;

		if ($pending === null || $provider === null)
		{
			throw new http_exception(Response::HTTP_GONE, 'SLAMB_EXTLOGIN_ERR_EXPIRED');
		}

		// The board settings may have changed since the visitor came back from the provider
		if (!$this->creator->is_registration_open())
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'UCP_REGISTER_DISABLE');
		}

		if ($this->creator->has_required_profile_fields())
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'SLAMB_EXTLOGIN_ERR_PROFILE_FIELDS');
		}

		// An address the provider vouches for is not editable
		$email_locked = (bool) $pending['email_verified'] && $pending['account_email'] !== '';

		$errors = [];
		$username = utf8_htmlspecialchars(mb_substr(trim($pending['display_name']), 0, (int) $this->config['max_name_chars']));
		$email = utf8_htmlspecialchars($pending['account_email']);
		$agreed = false;
		$adult = false;

		if ($this->request->is_set_post('submit'))
		{
			// request->variable() returns escaped strings, which is how phpBB stores usernames
			$username = $this->request->variable('username', '', true);
			$email = $email_locked ? $pending['account_email'] : strtolower($this->request->variable('email', ''));
			$agreed = $this->request->variable('agreed', false);
			$adult = $this->request->variable('adult', false);

			$errors = $this->validate($username, $email, $agreed, $adult);

			if (!$errors)
			{
				// Used up before the account exists, so a double click cannot create two
				if (!$this->states->delete_pending($token))
				{
					throw new http_exception(Response::HTTP_GONE, 'SLAMB_EXTLOGIN_ERR_EXPIRED');
				}

				return $this->finish($pending, $username, $email, $email_locked);
			}
		}

		add_form_key(self::FORM_KEY);

		$this->template->assign_vars([
			'ERROR'				=> implode('<br />', $errors),
			'USERNAME'			=> $username,
			'USERNAME_EXPLAIN'	=> $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', (int) $this->config['min_name_chars'], (int) $this->config['max_name_chars']),
			'EMAIL'				=> $email_locked ? utf8_htmlspecialchars($pending['account_email']) : $email,
			'PROVIDER_TITLE'	=> $provider->get_title(),
			'S_AGREED'			=> $agreed,
			'S_ADULT'			=> $adult,
			'S_COPPA'			=> (bool) $this->config['coppa_enable'],
			'S_EMAIL_LOCKED'	=> $email_locked,
			'S_ACTION'			=> $this->helper->route('slamb_externallogin_register', ['token' => $token]),
			'U_TERMS_USE'		=> append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=terms'),
			'U_PRIVACY'			=> append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=privacy'),
		]);

		return $this->helper->render('@slamb_externallogin/register.html', $this->language->lang('REGISTRATION'));
	}

	/**
	 * Same rules as the regular registration form, minus the password.
	 *
	 * @return string[] Localised error messages
	 */
	protected function validate(string $username, string $email, bool $agreed, bool $adult): array
	{
		if (!check_form_key(self::FORM_KEY))
		{
			return [$this->language->lang('FORM_INVALID')];
		}

		$errors = validate_data(
			['username' => $username, 'email' => $email],
			[
				'username'	=> [
					['string', false, (int) $this->config['min_name_chars'], (int) $this->config['max_name_chars']],
					['username', ''],
				],
				'email'		=> [
					['string', false, 6, 60],
					['user_email'],
				],
			]
		);

		$errors = array_map([$this->language, 'lang'], $errors);

		if (!$agreed)
		{
			$errors[] = $this->language->lang('SLAMB_EXTLOGIN_ERR_AGREE');
		}

		if ($this->config['coppa_enable'] && !$adult)
		{
			$errors[] = $this->language->lang('SLAMB_EXTLOGIN_ERR_AGE');
		}

		if ($this->config['check_dnsbl'] && ($dnsbl = $this->user->check_dnsbl('register')) !== false)
		{
			$errors[] = $this->language->lang('IP_BLACKLISTED', $this->user->ip, $dnsbl[1]);
		}

		return $errors;
	}

	/**
	 * Create the account and tell the visitor how it goes on.
	 */
	protected function finish(array $pending, string $username, string $email, bool $email_locked): Response
	{
		try
		{
			$result = $this->creator->create($username, $email, $email_locked);
		}
		catch (\RuntimeException $exception)
		{
			error_log('[slamb/externallogin] ' . $exception->getMessage());
			throw new http_exception(Response::HTTP_INTERNAL_SERVER_ERROR, 'SLAMB_EXTLOGIN_ERR_PROVIDER');
		}

		$this->accounts->link($result['user_id'], new identity(
			$pending['provider'],
			$pending['provider_uid'],
			$email,
			(bool) $pending['email_verified'],
			$pending['display_name']
		));
		$this->logger->registered((int) $result['user_id'], $pending['provider']);

		if ($result['status'] === user_creator::STATUS_ACTIVE)
		{
			// The account is active and the visitor is signed in already
			$redirect = (string) $pending['redirect'];
			redirect(reapply_sid($redirect !== '' ? $redirect : append_sid($this->root_path . 'index.' . $this->php_ext)));
		}

		$message_key = $result['status'] === user_creator::STATUS_INACTIVE_ADMIN ? 'ACCOUNT_INACTIVE_ADMIN' : 'ACCOUNT_INACTIVE';

		trigger_error(
			$this->language->lang($message_key) . '<br /><br />' .
			$this->language->lang('RETURN_INDEX', '<a href="' . append_sid($this->root_path . 'index.' . $this->php_ext) . '">', '</a>')
		);
	}
}
