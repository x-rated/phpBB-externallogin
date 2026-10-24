<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\migrations;

class install_schema extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v33x\v3319'];
	}

	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'slamb_extlogin_accounts');
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				// Link between a forum account and an account at an identity provider
				$this->table_prefix . 'slamb_extlogin_accounts' => [
					'COLUMNS' => [
						'account_id'	=> ['UINT', null, 'auto_increment'],
						'user_id'		=> ['UINT', 0],
						'provider'		=> ['VCHAR:20', ''],
						'provider_uid'	=> ['VCHAR:128', ''],
						'account_email'	=> ['VCHAR_UNI:100', ''],
						'linked_time'	=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'account_id',
					'KEYS'			=> [
						'provider_uid'	=> ['UNIQUE', ['provider', 'provider_uid']],
						'user_id'		=> ['INDEX', 'user_id'],
					],
				],
				// One row per started sign-in (state, nonce and PKCE verifier), used once
				$this->table_prefix . 'slamb_extlogin_states' => [
					'COLUMNS' => [
						'state'			=> ['VCHAR:64', ''],
						'provider'		=> ['VCHAR:20', ''],
						'session_id'	=> ['CHAR:32', ''],
						'nonce'			=> ['VCHAR:64', ''],
						'verifier'		=> ['VCHAR:128', ''],
						'redirect'		=> ['VCHAR:255', ''],
						'state_time'	=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'state',
					'KEYS'			=> [
						'state_time'	=> ['INDEX', 'state_time'],
					],
				],
				// A verified identity waiting for the visitor to finish the registration
				$this->table_prefix . 'slamb_extlogin_pending' => [
					'COLUMNS' => [
						'token'			=> ['VCHAR:64', ''],
						'provider'		=> ['VCHAR:20', ''],
						'provider_uid'	=> ['VCHAR:128', ''],
						'session_id'	=> ['CHAR:32', ''],
						'account_email'	=> ['VCHAR_UNI:100', ''],
						'email_verified'	=> ['BOOL', 0],
						'display_name'	=> ['VCHAR_UNI:100', ''],
						'redirect'		=> ['VCHAR:255', ''],
						'pending_time'	=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'token',
					'KEYS'			=> [
						'pending_time'	=> ['INDEX', 'pending_time'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'slamb_extlogin_accounts',
				$this->table_prefix . 'slamb_extlogin_states',
				$this->table_prefix . 'slamb_extlogin_pending',
			],
		];
	}
}
