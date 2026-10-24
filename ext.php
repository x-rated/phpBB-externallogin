<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin;

class ext extends \phpbb\extension\base
{
	/**
	 * The extension talks to the identity providers with cURL.
	 *
	 * {@inheritdoc}
	 */
	public function is_enableable()
	{
		return function_exists('curl_init');
	}
}
