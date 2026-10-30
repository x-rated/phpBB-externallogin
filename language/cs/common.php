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
	'SLAMB_EXTLOGIN_ERR_EMAIL_EXISTS'	=> 'Účet s touto e-mailovou adresou již existuje. Přihlaste se běžným způsobem a službu si přidejte v uživatelském panelu v části Profil > Propojené služby.',
	'SLAMB_EXTLOGIN_ERR_EXPIRED'		=> 'Platnost registrace vypršela. Začněte prosím znovu.',
	'SLAMB_EXTLOGIN_ERR_PROVIDER'		=> 'Přihlášení se nezdařilo. Zkuste to prosím později.',
	'SLAMB_EXTLOGIN_DELETED'				=> 'Propojení vašeho účtu na fóru s Facebookem bylo odstraněno. Samotný účet na fóru zůstává; pokud chcete smazat i ten, obraťte se na správce fóra.',
	'SLAMB_EXTLOGIN_ERR_STATE'			=> 'Požadavek na přihlášení je neplatný nebo vypršel. Zkuste to prosím znovu.',
	'UCP_SLAMB_EXTLOGIN_TITLE'			=> 'Propojené služby',
	'UCP_SLAMB_EXTLOGIN_EXPLAIN'		=> 'Propojte službu, abyste se mohli přihlašovat i přes ni, ne jen heslem. Každou službu lze propojit s jedním účtem.',
	'UCP_SLAMB_EXTLOGIN_NONE'			=> 'Žádná služba není dostupná.',
	'SLAMB_EXTLOGIN_LINK'				=> 'Propojit',
	'SLAMB_EXTLOGIN_UNLINK'				=> 'Odpojit',
	'SLAMB_EXTLOGIN_LINKED_SINCE'		=> 'Propojeno %s',
	'SLAMB_EXTLOGIN_LINKED'				=> 'Služba %s byla propojena s vaším účtem.',
	'SLAMB_EXTLOGIN_UNLINKED'			=> 'Služba %s byla od vašeho účtu odpojena.',
	'SLAMB_EXTLOGIN_UNLINK_CONFIRM'		=> 'Odpojit službu %s od vašeho účtu?',
	'SLAMB_EXTLOGIN_BACK'				=> 'Zpět na propojené služby',
	'SLAMB_EXTLOGIN_ERR_LAST_LINK'		=> 'Je to jediný způsob, jak se můžete přihlásit, protože váš účet nemá heslo. Nejdřív propojte jinou službu nebo si nastavte heslo (použijte „Zapomněl jsem heslo“).',
	'SLAMB_EXTLOGIN_ERR_LINKED_OTHER'	=> 'Tento účet je již propojen s jiným uživatelem tohoto fóra.',
	'SLAMB_EXTLOGIN_ERR_ALREADY_LINKED'	=> 'S vaším účtem je již propojen jiný účet této služby. Nejdřív jej odpojte.',
]);
