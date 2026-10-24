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
	'ACP_SLAMB_EXTLOGIN_TITLE'				=> 'Externí přihlášení',
	'ACP_SLAMB_EXTLOGIN_SETTINGS'			=> 'Externí přihlášení',
	'ACP_SLAMB_EXTLOGIN_SETTINGS_EXPLAIN'	=> 'Umožní návštěvníkům registraci a přihlášení pomocí existujícího účtu. U poskytovatele vytvořte aplikaci, do ní zkopírujte níže uvedenou přesměrovací adresu a sem vložte ID klienta a tajný klíč.',
	'ACP_SLAMB_EXTLOGIN_CONSOLE'			=> 'Otevřít konzoli pro vývojáře',
	'ACP_SLAMB_EXTLOGIN_REDIRECT_URI'		=> 'Přesměrovací adresa',
	'ACP_SLAMB_EXTLOGIN_CLIENT_ID'			=> 'ID klienta',
	'ACP_SLAMB_EXTLOGIN_SECRET'				=> 'Tajný klíč klienta',
	'ACP_SLAMB_EXTLOGIN_SECRET_SET'			=> 'Uloženo. Nechte prázdné, pokud jej chcete zachovat.',
	'ACP_SLAMB_EXTLOGIN_ENABLE'				=> 'Zapnout',
	'ACP_SLAMB_EXTLOGIN_ERR_CREDENTIALS'	=> '%s vyžaduje ID klienta i tajný klíč.',
	'ACP_SLAMB_EXTLOGIN_HELP_GOOGLE'		=> 'Vytvořte „ID klienta OAuth“ typu „Webová aplikace“ a přesměrovací adresu přidejte mezi autorizované identifikátory URI pro přesměrování.',
	'ACP_SLAMB_EXTLOGIN_HELP_MICROSOFT'		=> 'Zaregistruj aplikaci pro osobní i pracovní účty, přesměrovací adresu přidej jako URI typu „Web“ a vytvoř tajný klíč klienta (sloupec „Hodnota“).',
	'ACP_SLAMB_EXTLOGIN_HELP_FACEBOOK'		=> 'Vytvoř aplikaci typu „Consumer“ s produktem „Facebook Login“, přesměrovací adresu přidej mezi platné přesměrovací URI OAuth a adresu pro smazání dat jako URL zpětného volání pro smazání dat, a pak aplikaci přepni do režimu „Live“.',
	'ACP_SLAMB_EXTLOGIN_DELETION_URL'		=> 'URL zpětného volání pro smazání dat',
	'ACP_SLAMB_EXTLOGIN_DELETION_EXPLAIN'	=> 'Meta ji vyžaduje u živých aplikací.',
]);
