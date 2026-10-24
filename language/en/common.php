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
	'SLAMB_EXTLOGIN_ERR_EMAIL_EXISTS'	=> 'An account with this e-mail address already exists. Log in the usual way and link the service afterwards.',
	'SLAMB_EXTLOGIN_ERR_EXPIRED'		=> 'This sign-up has expired. Please start again.',
	'SLAMB_EXTLOGIN_ERR_PROFILE_FIELDS'	=> 'This board requires extra profile fields, so sign-up with an external service is not possible.',
	'SLAMB_EXTLOGIN_ERR_PROVIDER'		=> 'The sign-in failed. Please try again later.',
	'SLAMB_EXTLOGIN_DELETED'				=> 'The link between your forum account and Facebook has been removed. Your forum account itself stays; ask the board administrator if you want it deleted too.',
	'SLAMB_EXTLOGIN_ERR_STATE'			=> 'The sign-in request is invalid or has expired. Please try again.',
]);
