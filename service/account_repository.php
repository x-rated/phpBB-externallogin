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
 * Links between forum accounts and accounts at identity providers.
 */
class account_repository
{
	/** Groups whose members are never linked automatically by e-mail address */
	protected const PRIVILEGED_GROUPS = ['ADMINISTRATORS', 'GLOBAL_MODERATORS'];

	public function __construct(
		protected driver_interface $db,
		protected string $accounts_table,
		protected string $users_table,
		protected string $groups_table,
		protected string $user_group_table
	)
	{
	}

	/**
	 * @return int The forum user id linked to the provider account, 0 when there is none
	 */
	public function find_user_id(string $provider, string $uid): int
	{
		$sql = 'SELECT user_id
			FROM ' . $this->accounts_table . "
			WHERE provider = '" . $this->db->sql_escape($provider) . "'
				AND provider_uid = '" . $this->db->sql_escape($uid) . "'";
		$result = $this->db->sql_query($sql);
		$user_id = (int) $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);

		// A link left behind by a deleted account must not block the service forever
		if ($user_id && $this->get_user($user_id) === null)
		{
			$this->unlink_all($user_id);

			return 0;
		}

		return $user_id;
	}

	/**
	 * Remove every link of a forum account, for example when the account is deleted.
	 */
	public function unlink_all(int $user_id): void
	{
		$sql = 'DELETE FROM ' . $this->accounts_table . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * @return array|null Basic data of a forum user
	 */
	public function get_user(int $user_id): ?array
	{
		$sql = 'SELECT user_id, username, user_type, user_email
			FROM ' . $this->users_table . '
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: null;
	}

	/**
	 * Forum users registered with the e-mail address (compared case-insensitively).
	 *
	 * @return array[]
	 */
	public function find_users_by_email(string $email): array
	{
		$email = strtolower($email);

		$sql = 'SELECT user_id, username, user_type, user_email
			FROM ' . $this->users_table . "
			WHERE LOWER(user_email) = '" . $this->db->sql_escape($email) . "'";
		$result = $this->db->sql_query($sql);

		$users = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (strtolower($row['user_email']) === $email)
			{
				$users[] = $row;
			}
		}
		$this->db->sql_freeresult($result);

		return $users;
	}

	/**
	 * Founders, administrators and global moderators are too valuable a target to
	 * be linked by nothing more than a matching e-mail address.
	 */
	public function is_privileged(array $user): bool
	{
		if ((int) $user['user_type'] === USER_FOUNDER)
		{
			return true;
		}

		$sql = 'SELECT ug.user_id
			FROM ' . $this->user_group_table . ' ug, ' . $this->groups_table . ' g
			WHERE ug.user_id = ' . (int) $user['user_id'] . '
				AND ug.user_pending = 0
				AND g.group_id = ug.group_id
				AND g.group_type = ' . GROUP_SPECIAL . '
				AND ' . $this->db->sql_in_set('g.group_name', self::PRIVILEGED_GROUPS);
		$result = $this->db->sql_query_limit($sql, 1);
		$found = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $found;
	}

	/**
	 * Remove a link, for example after a data deletion request of the provider.
	 *
	 * @return int Number of removed links
	 */
	public function unlink(string $provider, string $uid): int
	{
		$sql = 'DELETE FROM ' . $this->accounts_table . "
			WHERE provider = '" . $this->db->sql_escape($provider) . "'
				AND provider_uid = '" . $this->db->sql_escape($uid) . "'";
		$this->db->sql_query($sql);

		return (int) $this->db->sql_affectedrows();
	}

	/**
	 * Services linked to a forum account.
	 *
	 * @return array[] Rows with provider, provider_uid and linked_time
	 */
	public function get_links(int $user_id): array
	{
		$sql = 'SELECT provider, provider_uid, linked_time
			FROM ' . $this->accounts_table . '
			WHERE user_id = ' . (int) $user_id . '
			ORDER BY linked_time';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows ?: [];
	}

	public function unlink_user(int $user_id, string $provider): int
	{
		$sql = 'DELETE FROM ' . $this->accounts_table . '
			WHERE user_id = ' . (int) $user_id . "
				AND provider = '" . $this->db->sql_escape($provider) . "'";
		$this->db->sql_query($sql);

		return (int) $this->db->sql_affectedrows();
	}

	public function link(int $user_id, identity $identity): void
	{
		$sql = 'INSERT INTO ' . $this->accounts_table . ' ' . $this->db->sql_build_array('INSERT', [
			'user_id'		=> $user_id,
			'provider'		=> $identity->provider,
			'provider_uid'	=> $identity->uid,
			'account_email'	=> mb_substr($identity->email, 0, 100),
			'linked_time'	=> time(),
		]);
		$this->db->sql_query($sql);
	}
}
