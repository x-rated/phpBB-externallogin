<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 * Czech
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
	'SLAMB_EXTLOGIN_OR_LOGIN'			=> 'Nebo se přihlaste přes',
	'SLAMB_EXTLOGIN_OR_REGISTER'		=> 'Nebo se zaregistrujte přes',
	'SLAMB_EXTLOGIN_REGISTER_TITLE'		=> 'Dokončení registrace přes %s',
	'SLAMB_EXTLOGIN_AGREE'				=> 'Souhlasím s <a href="%1$s">podmínkami použití</a> a <a href="%2$s">zásadami ochrany osobních údajů</a>.',
	'SLAMB_EXTLOGIN_ADULT'				=> 'Je mi alespoň 13 let.',
	'SLAMB_EXTLOGIN_ERR_AGREE'			=> 'Musíte souhlasit s podmínkami.',
	'SLAMB_EXTLOGIN_ERR_AGE'			=> 'Musíte potvrdit svůj věk.',
	'SLAMB_EXTLOGIN_ERR_CANCELLED'		=> 'Přihlášení bylo zrušeno.',
	'SLAMB_EXTLOGIN_ERR_DISABLED'		=> 'Tento způsob přihlášení není dostupný.',
	'SLAMB_EXTLOGIN_ERR_EMAIL_EXISTS'	=> 'Účet s touto e-mailovou adresou již existuje. Přihlaste se běžným způsobem a službu si propojte později.',
	'SLAMB_EXTLOGIN_ERR_EXPIRED'		=> 'Platnost registrace vypršela. Začněte prosím znovu.',
	'SLAMB_EXTLOGIN_ERR_PROFILE_FIELDS'	=> 'Toto fórum vyžaduje další pole profilu, registrace přes externí službu proto není možná.',
	'SLAMB_EXTLOGIN_ERR_PROVIDER'		=> 'Přihlášení se nezdařilo. Zkuste to prosím později.',
	'SLAMB_EXTLOGIN_DELETED'				=> 'Propojení vašeho účtu na fóru s Facebookem bylo odstraněno. Samotný účet na fóru zůstává; pokud chcete smazat i ten, obraťte se na správce fóra.',
	'SLAMB_EXTLOGIN_ERR_STATE'			=> 'Požadavek na přihlášení je neplatný nebo vypršel. Zkuste to prosím znovu.',
]);
