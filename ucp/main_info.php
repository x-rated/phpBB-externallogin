<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\ucp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\slamb\externallogin\ucp\main_module',
			'title'		=> 'UCP_SLAMB_EXTLOGIN_TITLE',
			'modes'		=> [
				'services'	=> [
					'title'	=> 'UCP_SLAMB_EXTLOGIN_TITLE',
					'auth'	=> 'ext_slamb/externallogin',
					'cat'	=> ['UCP_PROFILE'],
				],
			],
		];
	}
}
