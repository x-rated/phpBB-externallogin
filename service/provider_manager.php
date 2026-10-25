<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\service;

use phpbb\controller\helper;
use phpbb\di\service_collection;
use slamb\externallogin\provider\provider_interface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Knows every installed provider and which of them the admin switched on.
 */
class provider_manager
{
	/** @var provider_interface[]|null */
	protected ?array $providers = null;

	public function __construct(
		protected service_collection $collection,
		protected credentials $credentials,
		protected helper $helper
	)
	{
	}

	/**
	 * All installed providers, keyed by id.
	 *
	 * @return provider_interface[]
	 */
	public function all(): array
	{
		if ($this->providers === null)
		{
			$providers = [];
			foreach ($this->collection as $provider)
			{
				$providers[$provider->get_id()] = $provider;
			}

			uasort($providers, static fn (provider_interface $a, provider_interface $b): int => $a->get_sort_order() <=> $b->get_sort_order());
			$this->providers = $providers;
		}

		return $this->providers;
	}

	/**
	 * Providers that are switched on and have both credentials.
	 *
	 * @return provider_interface[]
	 */
	public function get_enabled_all(): array
	{
		return array_filter($this->all(), fn (provider_interface $provider): bool => $this->credentials->is_enabled($provider->get_id()));
	}

	public function get_enabled(string $id): ?provider_interface
	{
		return $this->get_enabled_all()[$id] ?? null;
	}

	/**
	 * Address the provider sends the visitor back to. It must match the one
	 * registered in the developer console, so no session id is ever appended.
	 */
	public function get_redirect_uri(string $id): string
	{
		return $this->helper->route('slamb_externallogin_callback', ['provider' => $id], false, '', UrlGeneratorInterface::ABSOLUTE_URL);
	}

	/**
	 * Link of a "continue with" button.
	 */
	public function get_start_url(string $id, string $redirect = ''): string
	{
		$params = ['provider' => $id];

		if ($redirect !== '')
		{
			$params['redirect'] = $redirect;
		}

		return $this->helper->route('slamb_externallogin_start', $params);
	}

	/**
	 * Address Meta calls when a Facebook user asks for deletion of the data an app holds.
	 */
	public function get_facebook_deletion_url(): string
	{
		return $this->helper->route('slamb_externallogin_facebook_deletion', [], false, '', UrlGeneratorInterface::ABSOLUTE_URL);
	}

	/**
	 * Link for a signed-in member who adds a service to the account. The hash proves the click came from the board.
	 */
	public function get_link_url(string $id): string
	{
		return $this->helper->route('slamb_externallogin_start', [
			'provider'	=> $id,
			'link'		=> 1,
			'hash'		=> generate_link_hash('slamb_externallogin_link'),
		]);
	}

	public function get_client_id(string $id): string
	{
		return $this->credentials->get_client_id($id);
	}

	public function get_secret(string $id): string
	{
		return $this->credentials->get_secret($id);
	}
}
