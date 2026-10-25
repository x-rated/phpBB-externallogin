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

class add_state_purpose extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\slamb\externallogin\migrations\install_config'];
	}

	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'slamb_extlogin_states', 'purpose');
	}

	public function update_schema()
	{
		return [
			// "login" for a visitor signing in or registering, "link" for a member adding a service
			'add_columns' => [
				$this->table_prefix . 'slamb_extlogin_states' => [
					'purpose'	=> ['VCHAR:10', 'login'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'slamb_extlogin_states' => ['purpose'],
			],
		];
	}
}
