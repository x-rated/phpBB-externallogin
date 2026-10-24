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

use phpbb\config\config;
use phpbb\config\db_text;

/**
 * Reads and stores the app credentials the admin enters in the ACP.
 *
 * The client ID lives in the board configuration, the secret in config_text.
 */
class credentials
{
	public function __construct(protected config $config, protected db_text $config_text)
	{
	}

	public function is_enabled(string $provider): bool
	{
		return (bool) $this->config['slamb_extlogin_' . $provider . '_enabled']
			&& $this->get_client_id($provider) !== ''
			&& $this->get_secret($provider) !== '';
	}

	public function is_switched_on(string $provider): bool
	{
		return (bool) $this->config['slamb_extlogin_' . $provider . '_enabled'];
	}

	public function get_client_id(string $provider): string
	{
		return trim((string) $this->config['slamb_extlogin_' . $provider . '_id']);
	}

	public function get_secret(string $provider): string
	{
		return trim((string) $this->config_text->get('slamb_extlogin_' . $provider . '_secret'));
	}

	public function has_secret(string $provider): bool
	{
		return $this->get_secret($provider) !== '';
	}

	/**
	 * @param string $provider
	 * @param bool $enabled
	 * @param string $client_id
	 * @param string|null $secret New secret, null keeps the stored one
	 */
	public function save(string $provider, bool $enabled, string $client_id, ?string $secret): void
	{
		$this->config->set('slamb_extlogin_' . $provider . '_enabled', $enabled ? 1 : 0);
		$this->config->set('slamb_extlogin_' . $provider . '_id', trim($client_id));

		if ($secret !== null)
		{
			$this->config_text->set('slamb_extlogin_' . $provider . '_secret', trim($secret));
		}
	}
}
