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
 * OpenID Connect authorisation code flow with PKCE. Google and Microsoft share it,
 * a provider only describes its endpoints and a few details.
 *
 * The ID token is received straight from the token endpoint over TLS, so its
 * signature is not checked (OpenID Connect Core, section 3.1.3.7). Issuer, audience,
 * expiry and nonce are checked.
 */
abstract class oidc_provider implements provider_interface
{
	protected const CLOCK_SKEW = 60;
	protected const MAX_UID_LENGTH = 128;

	public function __construct(protected http_client $http)
	{
	}

	abstract protected function get_authorization_endpoint(): string;

	abstract protected function get_token_endpoint(): string;

	abstract protected function get_scopes(): string;

	/**
	 * @param string $issuer The "iss" claim of the ID token
	 * @param array $claims All claims of the ID token
	 */
	abstract protected function is_valid_issuer(string $issuer, array $claims): bool;

	/**
	 * Endpoint for claims missing in the ID token. Empty when there is none.
	 */
	protected function get_userinfo_endpoint(): string
	{
		return '';
	}

	/**
	 * Extra parameters of the authorisation request.
	 */
	protected function get_extra_authorize_params(): array
	{
		return [];
	}

	/**
	 * Whether the provider vouches for the e-mail address in the claims.
	 */
	protected function is_email_verified(array $claims): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_authorize_url(string $client_id, string $redirect_uri, string $state, string $nonce, string $code_challenge): string
	{
		$params = [
			'response_type'			=> 'code',
			'client_id'				=> $client_id,
			'redirect_uri'			=> $redirect_uri,
			'scope'					=> $this->get_scopes(),
			'state'					=> $state,
			'nonce'					=> $nonce,
			'code_challenge'		=> $code_challenge,
			'code_challenge_method'	=> 'S256',
		] + $this->get_extra_authorize_params();

		return $this->get_authorization_endpoint() . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch_identity(string $client_id, string $client_secret, string $redirect_uri, string $code, string $code_verifier, string $nonce): identity
	{
		$tokens = $this->http->post_form($this->get_token_endpoint(), [
			'grant_type'	=> 'authorization_code',
			'code'			=> $code,
			'redirect_uri'	=> $redirect_uri,
			'client_id'		=> $client_id,
			'client_secret'	=> $client_secret,
			'code_verifier'	=> $code_verifier,
		]);

		if (empty($tokens['id_token']) || !is_string($tokens['id_token']))
		{
			throw new provider_exception('The token answer has no ID token');
		}

		$claims = $this->decode_id_token($tokens['id_token']);
		$this->validate_claims($claims, $client_id, $nonce);

		$email = isset($claims['email']) && is_string($claims['email']) ? $claims['email'] : '';
		$verified = $email !== '' && $this->is_email_verified($claims);

		// Some providers leave the e-mail address out of the ID token
		$userinfo = $this->get_userinfo_endpoint();
		if ($email === '' && $userinfo !== '' && !empty($tokens['access_token']) && is_string($tokens['access_token']))
		{
			$info = $this->http->get_json($userinfo, $tokens['access_token']);

			if (isset($info['sub']) && (string) $info['sub'] === (string) $claims['sub'] && isset($info['email']) && is_string($info['email']))
			{
				$email = $info['email'];
				$verified = $this->is_email_verified($info);
			}
		}

		$name = '';
		foreach (['name', 'given_name', 'preferred_username'] as $key)
		{
			if (!empty($claims[$key]) && is_string($claims[$key]))
			{
				$name = $claims[$key];
				break;
			}
		}

		return new identity($this->get_id(), (string) $claims['sub'], $email, $verified, $name);
	}

	/**
	 * @throws provider_exception
	 */
	protected function decode_id_token(string $token): array
	{
		$parts = explode('.', $token);

		if (count($parts) !== 3)
		{
			throw new provider_exception('The ID token is malformed');
		}

		$payload = base64_decode(strtr($parts[1], '-_', '+/'), true);
		$claims = is_string($payload) ? json_decode($payload, true) : null;

		if (!is_array($claims))
		{
			throw new provider_exception('The ID token payload is not valid');
		}

		return $claims;
	}

	/**
	 * @throws provider_exception
	 */
	protected function validate_claims(array $claims, string $client_id, string $nonce): void
	{
		if (empty($claims['iss']) || !is_string($claims['iss']) || !$this->is_valid_issuer($claims['iss'], $claims))
		{
			throw new provider_exception('Unexpected issuer of the ID token');
		}

		$audience = $claims['aud'] ?? null;
		$audience_ok = is_array($audience) ? in_array($client_id, $audience, true) : $audience === $client_id;
		if (!$audience_ok)
		{
			throw new provider_exception('The ID token was issued for another client');
		}

		if (!isset($claims['exp']) || !is_numeric($claims['exp']) || (int) $claims['exp'] < time() - self::CLOCK_SKEW)
		{
			throw new provider_exception('The ID token has expired');
		}

		if (empty($claims['nonce']) || !is_string($claims['nonce']) || !hash_equals($nonce, $claims['nonce']))
		{
			throw new provider_exception('The nonce of the ID token does not match');
		}

		if (empty($claims['sub']) || !is_string($claims['sub']) || strlen($claims['sub']) > self::MAX_UID_LENGTH)
		{
			throw new provider_exception('The ID token has no usable subject');
		}
	}
}
