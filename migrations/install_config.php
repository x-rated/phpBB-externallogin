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

class install_config extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\slamb\externallogin\migrations\install_schema'];
	}

	public function update_data()
	{
		$data = [];

		foreach (['google', 'microsoft', 'facebook'] as $provider)
		{
			$data[] = ['config.add', ['slamb_extlogin_' . $provider . '_enabled', 0]];
			$data[] = ['config.add', ['slamb_extlogin_' . $provider . '_id', '']];
			// The secret goes to config_text, so it is not kept in the cached board configuration
			$data[] = ['config_text.add', ['slamb_extlogin_' . $provider . '_secret', '']];
		}

		// Next to the "Authentication" page in General > Client communication
		$data[] = ['module.add', [
			'acp',
			'ACP_CLIENT_COMMUNICATION',
			[
				'module_basename'	=> '\slamb\externallogin\acp\main_module',
				'modes'				=> ['settings'],
			],
		]];

		return $data;
	}
}
