<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\provider;

use slamb\externallogin\exception\provider_exception;
use slamb\externallogin\service\http_client;

/**
 * Facebook Login (Meta). Plain OAuth 2.0 with a lookup of the profile in the Graph API.
 * The URLs carry no version, so Meta serves the version the app was created with.
 */
class facebook implements provider_interface
{
	protected const MAX_UID_LENGTH = 128;

	public function __construct(protected http_client $http)
	{
	}

	public function get_id(): string
	{
		return 'facebook';
	}

	public function get_title(): string
	{
		return 'Facebook';
	}

	public function get_sort_order(): int
	{
		return 30;
	}

	public function get_help_lang_key(): string
	{
		return 'ACP_SLAMB_EXTLOGIN_HELP_FACEBOOK';
	}

	public function get_console_url(): string
	{
		return 'https://developers.facebook.com/apps/';
	}

	public function get_authorize_url(string $client_id, string $redirect_uri, string $state, string $nonce, string $code_challenge): string
	{
		return $this->get_authorization_endpoint() . '?' . http_build_query([
			'response_type'			=> 'code',
			'client_id'				=> $client_id,
			'redirect_uri'			=> $redirect_uri,
			'scope'					=> 'public_profile,email',
			'state'					=> $state,
			'code_challenge'		=> $code_challenge,
			'code_challenge_method'	=> 'S256',
		], '', '&', PHP_QUERY_RFC3986);
	}

	public function fetch_identity(string $client_id, string $client_secret, string $redirect_uri, string $code, string $code_verifier, string $nonce): identity
	{
		$tokens = $this->http->post_form($this->get_token_endpoint(), [
			'code'			=> $code,
			'redirect_uri'	=> $redirect_uri,
			'client_id'		=> $client_id,
			'client_secret'	=> $client_secret,
			'code_verifier'	=> $code_verifier,
		]);

		if (empty($tokens['access_token']) || !is_string($tokens['access_token']))
		{
			throw new provider_exception('The token answer has no access token');
		}

		$profile = $this->http->get_json($this->get_profile_endpoint() . '?' . http_build_query([
			'fields'		=> 'id,name,email',
			'access_token'	=> $tokens['access_token'],
		], '', '&', PHP_QUERY_RFC3986), '');

		if (empty($profile['id']) || !is_scalar($profile['id']) || strlen((string) $profile['id']) > self::MAX_UID_LENGTH)
		{
			throw new provider_exception('The profile has no usable id');
		}

		$email = isset($profile['email']) && is_string($profile['email']) ? $profile['email'] : '';
		$name = isset($profile['name']) && is_string($profile['name']) ? $profile['name'] : '';

		// An address from Facebook is never treated as verified
		return new identity($this->get_id(), (string) $profile['id'], $email, false, $name);
	}

	protected function get_authorization_endpoint(): string
	{
		return 'https://www.facebook.com/dialog/oauth';
	}

	protected function get_token_endpoint(): string
	{
		return 'https://graph.facebook.com/oauth/access_token';
	}

	protected function get_profile_endpoint(): string
	{
		return 'https://graph.facebook.com/me';
	}
}
