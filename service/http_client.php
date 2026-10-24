<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\service;

use slamb\externallogin\exception\provider_exception;

/**
 * Minimal JSON-over-HTTPS client built on cURL. Only the fixed endpoints of the
 * identity providers are ever requested, never an address given by a visitor.
 */
class http_client
{
	protected const TIMEOUT = 10;
	protected const CONNECT_TIMEOUT = 5;

	/**
	 * POST an application/x-www-form-urlencoded body and decode the JSON answer.
	 *
	 * @param string $url
	 * @param array $fields
	 * @return array
	 * @throws provider_exception
	 */
	public function post_form(string $url, array $fields): array
	{
		return $this->request($url, [
			CURLOPT_POST		=> true,
			CURLOPT_POSTFIELDS	=> http_build_query($fields, '', '&'),
		]);
	}

	/**
	 * GET a JSON document with a bearer token (none when the token is empty, e.g. when it is part of the URL).
	 *
	 * @param string $url
	 * @param string $access_token
	 * @return array
	 * @throws provider_exception
	 */
	public function get_json(string $url, string $access_token): array
	{
		return $this->request($url, [
			CURLOPT_HTTPHEADER => $access_token === ''
				? ['Accept: application/json']
				: ['Accept: application/json', 'Authorization: Bearer ' . $access_token],
		]);
	}

	/**
	 * @param string $url
	 * @param array $options Extra cURL options
	 * @return array
	 * @throws provider_exception
	 */
	protected function request(string $url, array $options): array
	{
		$handle = curl_init($url);

		curl_setopt_array($handle, $options + [
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_FOLLOWLOCATION	=> false,
			CURLOPT_TIMEOUT			=> self::TIMEOUT,
			CURLOPT_CONNECTTIMEOUT	=> self::CONNECT_TIMEOUT,
			CURLOPT_SSL_VERIFYPEER	=> true,
			CURLOPT_SSL_VERIFYHOST	=> 2,
			CURLOPT_HTTPHEADER		=> ['Accept: application/json'],
			CURLOPT_USERAGENT		=> 'phpBB-slamb-externallogin',
		]);

		$body = curl_exec($handle);
		$status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
		$error = curl_error($handle);

		$data = is_string($body) ? json_decode($body, true) : null;

		if ($body === false)
		{
			throw new provider_exception('HTTP request failed: ' . $error);
		}

		if ($status < 200 || $status >= 300)
		{
			// Providers describe OAuth errors as {"error": "invalid_client", ...}
			$reason = (is_array($data) && isset($data['error']) && is_string($data['error'])) ? $data['error'] : 'no details';
			throw new provider_exception('HTTP status ' . $status . ' (' . $reason . ')');
		}

		if (!is_array($data))
		{
			throw new provider_exception('The answer is not valid JSON');
		}

		return $data;
	}
}
