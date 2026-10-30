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
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\notification\manager as notification_manager;
use phpbb\user;

/**
 * Creates forum accounts the way the regular registration form does, so every
 * setting of the board (activation, "newly registered" group, e-mails) is respected.
 * The only difference is that the visitor has no password.
 */
class user_creator
{
	public const STATUS_ACTIVE = 'active';
	public const STATUS_INACTIVE_USER = 'inactive_user';
	public const STATUS_INACTIVE_ADMIN = 'inactive_admin';

	public function __construct(
		protected config $config,
		protected driver_interface $db,
		protected language $language,
		protected notification_manager $notification_manager,
		protected user $user,
		protected string $groups_table,
		protected string $root_path,
		protected string $php_ext
	)
	{
	}

	/**
	 * Whether the board accepts new accounts at all ("Account activation" setting).
	 */
	public function is_registration_open(): bool
	{
		$mode = (int) $this->config['require_activation'];

		if ($mode === USER_ACTIVATION_DISABLE)
		{
			return false;
		}

		// Activation mails cannot be sent, so the regular form is closed as well
		return !(in_array($mode, [USER_ACTIVATION_SELF, USER_ACTIVATION_ADMIN], true) && !$this->config['email_enable']);
	}

	/**
	 * Create the account. Active accounts are signed in right away.
	 *
	 * @param string $username Already validated
	 * @param string $email Already validated
	 * @param bool $email_verified Whether the identity provider vouches for the address
	 * @param array $cp_data Validated custom profile field data of the registration form
	 * @return array{user_id: int, status: string}
	 */
	public function create(string $username, string $email, bool $email_verified, array $cp_data = []): array
	{
		include_once $this->root_path . 'includes/functions_user.' . $this->php_ext;

		$mode = (int) $this->config['require_activation'];
		$email_enabled = (bool) $this->config['email_enable'];

		if ($mode === USER_ACTIVATION_ADMIN && $email_enabled)
		{
			$status = self::STATUS_INACTIVE_ADMIN;
		}
		else if ($mode === USER_ACTIVATION_SELF && $email_enabled && !$email_verified)
		{
			$status = self::STATUS_INACTIVE_USER;
		}
		else
		{
			$status = self::STATUS_ACTIVE;
		}

		$sql = 'SELECT group_id
			FROM ' . $this->groups_table . "
			WHERE group_name = 'REGISTERED'
				AND group_type = " . GROUP_SPECIAL;
		$result = $this->db->sql_query($sql);
		$group_id = (int) $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if (!$group_id)
		{
			throw new \RuntimeException('The REGISTERED group is missing');
		}

		$active = $status === self::STATUS_ACTIVE;
		$activation_key = $active ? '' : strtolower(gen_rand_string(32));

		$user_row = [
			'username'					=> $username,
			'user_password'				=> '',
			'user_email'				=> $email,
			'group_id'					=> $group_id,
			'user_timezone'				=> $this->config['board_timezone'],
			'user_lang'					=> $this->user->lang_name,
			'user_type'					=> $active ? USER_NORMAL : USER_INACTIVE,
			'user_actkey'				=> $activation_key,
			'user_actkey_expiration'	=> $this->user::get_token_expiration(),
			'user_ip'					=> $this->user->ip,
			'user_regdate'				=> time(),
			'user_inactive_reason'		=> $active ? 0 : INACTIVE_REGISTER,
			'user_inactive_time'		=> $active ? 0 : time(),
		];

		if ($this->config['new_member_post_limit'])
		{
			$user_row['user_new'] = 1;
		}

		$user_id = user_add($user_row, $cp_data ?: false);

		if ($user_id === false)
		{
			throw new \RuntimeException('The account could not be created');
		}

		if ($email_enabled)
		{
			$this->send_welcome_mail($status, $user_id, $username, $email, $activation_key);
		}

		if ($status === self::STATUS_INACTIVE_ADMIN)
		{
			$this->notification_manager->add_notifications('notification.type.admin_activate_user', [
				'user_id'		=> $user_id,
				'user_actkey'	=> $user_row['user_actkey'],
				'user_regdate'	=> $user_row['user_regdate'],
			]);
		}

		if ($active)
		{
			$this->user->session_create($user_id, 0, false, 1);
		}

		return ['user_id' => (int) $user_id, 'status' => $status];
	}

	protected function send_welcome_mail(string $status, int $user_id, string $username, string $email, string $activation_key): void
	{
		include_once $this->root_path . 'includes/functions_messenger.' . $this->php_ext;

		$template = match ($status)
		{
			self::STATUS_INACTIVE_USER	=> 'user_welcome_inactive',
			self::STATUS_INACTIVE_ADMIN	=> 'admin_welcome_inactive',
			default						=> 'user_welcome',
		};

		$messenger = new \messenger(false);
		$messenger->template($template, $this->user->lang_name);
		$messenger->to($email, $username);
		$messenger->anti_abuse_headers($this->config, $this->user);
		$messenger->assign_vars([
			'WELCOME_MSG'	=> html_entity_decode(sprintf($this->language->lang('WELCOME_SUBJECT'), $this->config['sitename']), ENT_COMPAT),
			'USERNAME'		=> html_entity_decode($username, ENT_COMPAT),
			'U_ACTIVATE'	=> generate_board_url() . '/ucp.' . $this->php_ext . '?mode=activate&u=' . $user_id . '&k=' . $activation_key,
		]);
		$messenger->send(NOTIFY_EMAIL);
	}
}
