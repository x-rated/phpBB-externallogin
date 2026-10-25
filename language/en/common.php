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
	'SLAMB_EXTLOGIN_OR_LOGIN'			=> 'Or log in with',
	'SLAMB_EXTLOGIN_OR_REGISTER'		=> 'Or sign up with',
	'SLAMB_EXTLOGIN_REGISTER_TITLE'		=> 'Finish signing up with %s',
	'SLAMB_EXTLOGIN_AGREE'				=> 'I agree to the <a href="%1$s">terms of use</a> and <a href="%2$s">privacy policy</a>.',
	'SLAMB_EXTLOGIN_ADULT'				=> 'I am at least 13 years old.',
	'SLAMB_EXTLOGIN_ERR_AGREE'			=> 'You have to agree to the terms.',
	'SLAMB_EXTLOGIN_ERR_AGE'			=> 'You have to confirm your age.',
	'SLAMB_EXTLOGIN_ERR_CANCELLED'		=> 'The sign-in was cancelled.',
	'SLAMB_EXTLOGIN_ERR_DISABLED'		=> 'This sign-in method is not available.',
	'SLAMB_EXTLOGIN_ERR_EMAIL_EXISTS'	=> 'An account with this e-mail address already exists. Log in the usual way and add the service in the User Control Panel under Profile > Linked services.',
	'SLAMB_EXTLOGIN_ERR_EXPIRED'		=> 'This sign-up has expired. Please start again.',
	'SLAMB_EXTLOGIN_ERR_PROFILE_FIELDS'	=> 'This board requires extra profile fields, so sign-up with an external service is not possible.',
	'SLAMB_EXTLOGIN_ERR_PROVIDER'		=> 'The sign-in failed. Please try again later.',
	'SLAMB_EXTLOGIN_DELETED'				=> 'The link between your forum account and Facebook has been removed. Your forum account itself stays; ask the board administrator if you want it deleted too.',
	'SLAMB_EXTLOGIN_ERR_STATE'			=> 'The sign-in request is invalid or has expired. Please try again.',
	'UCP_SLAMB_EXTLOGIN_TITLE'			=> 'Linked services',
	'UCP_SLAMB_EXTLOGIN_EXPLAIN'		=> 'Link a service to log in with it as well as with your password. Each service can be linked to one account.',
	'UCP_SLAMB_EXTLOGIN_NONE'			=> 'No service is available.',
	'SLAMB_EXTLOGIN_LINK'				=> 'Link',
	'SLAMB_EXTLOGIN_UNLINK'				=> 'Unlink',
	'SLAMB_EXTLOGIN_LINKED_SINCE'		=> 'Linked on %s',
	'SLAMB_EXTLOGIN_LINKED'				=> '%s has been linked to your account.',
	'SLAMB_EXTLOGIN_UNLINKED'			=> '%s has been unlinked from your account.',
	'SLAMB_EXTLOGIN_UNLINK_CONFIRM'		=> 'Unlink %s from your account?',
	'SLAMB_EXTLOGIN_BACK'				=> 'Back to linked services',
	'SLAMB_EXTLOGIN_ERR_LAST_LINK'		=> 'This is the only way you can log in, because your account has no password. Link another service or set a password first (use “I forgot my password”).',
	'SLAMB_EXTLOGIN_ERR_LINKED_OTHER'	=> 'This account is already linked to another user of this board.',
	'SLAMB_EXTLOGIN_ERR_ALREADY_LINKED'	=> 'Another account of this service is already linked to your account. Unlink it first.',
]);
