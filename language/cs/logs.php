<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 * Czech (log entries)
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
	'LOG_SLAMB_EXTLOGIN_SETTINGS'	=> '<strong>Změněno nastavení externího přihlášení</strong><br />» Zapnuté služby: %s',
	'LOG_SLAMB_EXTLOGIN_REGISTERED'	=> '<strong>Účet vytvořen přes externí službu</strong><br />» %s',
	'LOG_SLAMB_EXTLOGIN_LINKED'		=> '<strong>K účtu propojena externí služba</strong><br />» %s',
	'LOG_SLAMB_EXTLOGIN_UNLINKED'	=> '<strong>Od účtu odpojena externí služba</strong><br />» %s',
	'LOG_SLAMB_EXTLOGIN_REFUSED'	=> '<strong>Přihlášení přes externí službu odmítnuto: e-mailová adresa odpovídá tomuto účtu, ale nestačí jako důkaz</strong><br />» %s',
]);
