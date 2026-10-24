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

use phpbb\db\driver\driver_interface;
use slamb\externallogin\provider\identity;

/**
 * Server-side memory of sign-ins in progress.
 *
 * Both kinds of records are tied to the phpBB session of the visitor, are used
 * once and expire quickly. Nothing a visitor could tamper with travels in the browser
 * except a random token.
 */
class state_storage
{
	/** Seconds a started sign-in may take (visitor at the provider) */
	public const STATE_TTL = 600;

	/** Seconds the visitor has to finish the registration form */
	public const PENDING_TTL = 1800;

	public function __construct(
		protected driver_interface $db,
		protected string $states_table,
		protected string $pending_table
	)
	{
	}

	/**
	 * Remember a started sign-in and return its state value.
	 */
	public function create_state(string $provider, string $session_id, string $nonce, string $verifier, string $redirect): string
	{
		$this->purge();

		$state = bin2hex(random_bytes(24));

		$sql = 'INSERT INTO ' . $this->states_table . ' ' . $this->db->sql_build_array('INSERT', [
			'state'			=> $state,
			'provider'		=> $provider,
			'session_id'	=> $session_id,
			'nonce'			=> $nonce,
			'verifier'		=> $verifier,
			'redirect'		=> $redirect,
			'state_time'	=> time(),
		]);
		$this->db->sql_query($sql);

		return $state;
	}

	/**
	 * Take a state out of the storage. A state can be consumed only once.
	 *
	 * @return array|null The record, or null when it is unknown, used or expired
	 */
	public function consume_state(string $state): ?array
	{
		$sql = 'SELECT *
			FROM ' . $this->states_table . "
			WHERE state = '" . $this->db->sql_escape($state) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return null;
		}

		$sql = 'DELETE FROM ' . $this->states_table . "
			WHERE state = '" . $this->db->sql_escape($state) . "'";
		$this->db->sql_query($sql);

		// If a parallel request already took it, this one lost
		if ($this->db->sql_affectedrows() < 1)
		{
			return null;
		}

		if ((int) $row['state_time'] < time() - self::STATE_TTL)
		{
			return null;
		}

		return $row;
	}

	/**
	 * Park a verified identity until the visitor completes the registration.
	 *
	 * @return string The token for the registration page
	 */
	public function create_pending(identity $identity, string $session_id, string $redirect): string
	{
		$token = bin2hex(random_bytes(16));

		$sql = 'INSERT INTO ' . $this->pending_table . ' ' . $this->db->sql_build_array('INSERT', [
			'token'			=> $token,
			'provider'		=> $identity->provider,
			'provider_uid'	=> $identity->uid,
			'session_id'	=> $session_id,
			'account_email'	=> mb_substr($identity->email, 0, 100),
			'email_verified'	=> $identity->email_verified ? 1 : 0,
			'display_name'	=> mb_substr($identity->name, 0, 100),
			'redirect'		=> $redirect,
			'pending_time'	=> time(),
		]);
		$this->db->sql_query($sql);

		return $token;
	}

	/**
	 * @return array|null The record when it exists, is fresh and belongs to this session
	 */
	public function get_pending(string $token, string $session_id): ?array
	{
		$sql = 'SELECT *
			FROM ' . $this->pending_table . "
			WHERE token = '" . $this->db->sql_escape($token) . "'
				AND pending_time > " . (time() - self::PENDING_TTL);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row || !hash_equals((string) $row['session_id'], $session_id))
		{
			return null;
		}

		return $row;
	}

	/**
	 * Use up a pending identity.
	 *
	 * @return bool False when it was already gone, e.g. taken by a parallel request
	 */
	public function delete_pending(string $token): bool
	{
		$sql = 'DELETE FROM ' . $this->pending_table . "
			WHERE token = '" . $this->db->sql_escape($token) . "'";
		$this->db->sql_query($sql);

		return $this->db->sql_affectedrows() > 0;
	}

	/**
	 * Drop expired records. Called when a new sign-in starts, so no cron is needed.
	 */
	protected function purge(): void
	{
		$sql = 'DELETE FROM ' . $this->states_table . '
			WHERE state_time < ' . (time() - self::STATE_TTL);
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->pending_table . '
			WHERE pending_time < ' . (time() - self::PENDING_TTL);
		$this->db->sql_query($sql);
	}
}
