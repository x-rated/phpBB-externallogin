<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 * English
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'UCP_SLAMB_EXTLOGIN_TITLE'	=> 'Linked services',
]);
