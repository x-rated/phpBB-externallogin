<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 * English (log entries)
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
	'LOG_SLAMB_EXTLOGIN_SETTINGS'	=> '<strong>External login settings changed</strong><br />» Services switched on: %s',
	'LOG_SLAMB_EXTLOGIN_REGISTERED'	=> '<strong>Account created through an external service</strong><br />» %s',
	'LOG_SLAMB_EXTLOGIN_LINKED'		=> '<strong>External service linked to the account</strong><br />» %s',
	'LOG_SLAMB_EXTLOGIN_UNLINKED'	=> '<strong>External service unlinked from the account</strong><br />» %s',
	'LOG_SLAMB_EXTLOGIN_REFUSED'	=> '<strong>Sign-in through an external service refused: the e-mail address matches this account, but is not proof enough</strong><br />» %s',
]);
