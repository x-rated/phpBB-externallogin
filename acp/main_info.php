<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\slamb\externallogin\acp\main_module',
			'title'		=> 'ACP_SLAMB_EXTLOGIN_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_SLAMB_EXTLOGIN_SETTINGS',
					'auth'	=> 'ext_slamb/externallogin && acl_a_board',
					'cat'	=> ['ACP_SLAMB_EXTLOGIN_TITLE'],
				],
			],
		];
	}
}
