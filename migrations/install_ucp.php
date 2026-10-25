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

class install_ucp extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\slamb\externallogin\migrations\add_state_purpose'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'ucp',
				'UCP_PROFILE',
				[
					'module_basename'	=> '\slamb\externallogin\ucp\main_module',
					'modes'				=> ['services'],
				],
			]],
		];
	}
}
