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

use phpbb\log\log_interface;
use phpbb\user;

/**
 * Entries in the board's own logs. Each entry carries the IP address of the request, as every phpBB log does.
 * Nothing secret is logged, and successful sign-ins are not (the core does not log them either).
 */
class logger
{
	public function __construct(
		protected log_interface $log,
		protected user $user
	)
	{
	}

	/**
	 * Admin log: the settings page was saved.
	 *
	 * @param string[] $enabled Titles of the services that are switched on
	 */
	public function settings_changed(array $enabled): void
	{
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SLAMB_EXTLOGIN_SETTINGS', time(), [$enabled ? implode(', ', $enabled) : '-']);
	}

	/**
	 * User log: an account was created through a service.
	 */
	public function registered(int $user_id, string $provider): void
	{
		$this->user_entry($user_id, 'LOG_SLAMB_EXTLOGIN_REGISTERED', $provider);
	}

	public function linked(int $user_id, string $provider): void
	{
		$this->user_entry($user_id, 'LOG_SLAMB_EXTLOGIN_LINKED', $provider);
	}

	public function unlinked(int $user_id, string $provider): void
	{
		$this->user_entry($user_id, 'LOG_SLAMB_EXTLOGIN_UNLINKED', $provider);
	}

	/**
	 * User log of the account concerned: someone signed in at a service with a matching e-mail address
	 * and was turned away, because the address is not proof enough to take the account over.
	 */
	public function refused(int $user_id, string $provider): void
	{
		$this->user_entry($user_id, 'LOG_SLAMB_EXTLOGIN_REFUSED', $provider);
	}

	protected function user_entry(int $user_id, string $key, string $provider): void
	{
		$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $key, time(), [
			'reportee_id'	=> $user_id,
			$provider,
		]);
	}
}
