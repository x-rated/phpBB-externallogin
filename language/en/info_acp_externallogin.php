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
	'ACP_SLAMB_EXTLOGIN_TITLE'				=> 'External login',
	'ACP_SLAMB_EXTLOGIN_SETTINGS'			=> 'External login',
	'ACP_SLAMB_EXTLOGIN_SETTINGS_EXPLAIN'	=> 'Let visitors sign up and log in with an existing account. Create an app at the provider, copy the redirect address below into it, then paste the client ID and secret here.',
	'ACP_SLAMB_EXTLOGIN_CONSOLE'			=> 'Open the developer console',
	'ACP_SLAMB_EXTLOGIN_REDIRECT_URI'		=> 'Redirect address',
	'ACP_SLAMB_EXTLOGIN_CLIENT_ID'			=> 'Client ID',
	'ACP_SLAMB_EXTLOGIN_SECRET'				=> 'Client secret',
	'ACP_SLAMB_EXTLOGIN_SECRET_SET'			=> 'Saved. Leave empty to keep it.',
	'ACP_SLAMB_EXTLOGIN_ENABLE'				=> 'Enable',
	'ACP_SLAMB_EXTLOGIN_ERR_CREDENTIALS'	=> '%s needs both a client ID and a client secret.',
	'ACP_SLAMB_EXTLOGIN_HELP_GOOGLE'		=> 'Create an “OAuth client ID” of type “Web application” and add the redirect address as an authorised redirect URI.',
	'ACP_SLAMB_EXTLOGIN_HELP_MICROSOFT'		=> 'Register an app for personal and work accounts, add the redirect address as a “Web” redirect URI and create a client secret (the “Value” column).',
	'ACP_SLAMB_EXTLOGIN_HELP_FACEBOOK'		=> 'Create a “Consumer” app with the “Facebook Login” product, add the redirect address as a valid OAuth redirect URI and the deletion address as the data deletion callback URL, then switch the app to “Live”.',
	'ACP_SLAMB_EXTLOGIN_DELETION_URL'		=> 'Data deletion callback URL',
	'ACP_SLAMB_EXTLOGIN_DELETION_EXPLAIN'	=> 'Required by Meta for live apps.',
]);
