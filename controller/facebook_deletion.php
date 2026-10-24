<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\controller;

use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\request\request_interface;
use slamb\externallogin\service\account_repository;
use slamb\externallogin\service\provider_manager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Data deletion callback that Meta requires of every live app.
 *
 * The extension only keeps the link between the forum account and the Facebook
 * user, so a deletion request removes that link. The forum account stays.
 */
class facebook_deletion
{
	public function __construct(
		protected helper $helper,
		protected language $language,
		protected request_interface $request,
		protected provider_manager $providers,
		protected account_repository $accounts
	)
	{
	}

	public function request(): Response
	{
		$secret = $this->providers->get_secret('facebook');
		$payload = $secret !== '' ? $this->parse_signed_request((string) $this->request->variable('signed_request', '', true), $secret) : null;

		if ($payload === null || empty($payload['user_id']) || !is_scalar($payload['user_id']))
		{
			return new JsonResponse(['error' => 'invalid_request'], Response::HTTP_BAD_REQUEST);
		}

		$uid = (string) $payload['user_id'];
		$this->accounts->unlink('facebook', $uid);

		$code = substr(hash_hmac('sha256', 'deletion|' . $uid, $secret), 0, 24);

		return new JsonResponse([
			'url'					=> $this->helper->route('slamb_externallogin_deleted', ['code' => $code], false, '', UrlGeneratorInterface::ABSOLUTE_URL),
			'confirmation_code'		=> $code,
		]);
	}

	/**
	 * Page the "url" of the answer points to. It states a fact only; nothing is read from the code.
	 */
	public function deleted(string $code): Response
	{
		$this->language->add_lang('common', 'slamb/externallogin');

		return $this->helper->message('SLAMB_EXTLOGIN_DELETED');
	}

	/**
	 * @return array|null The payload when the signature matches
	 */
	protected function parse_signed_request(string $signed_request, string $secret): ?array
	{
		$parts = explode('.', $signed_request, 2);

		if (count($parts) !== 2)
		{
			return null;
		}

		$signature = base64_decode(strtr($parts[0], '-_', '+/'), true);
		$payload = base64_decode(strtr($parts[1], '-_', '+/'), true);
		$data = is_string($payload) ? json_decode($payload, true) : null;

		if ($signature === false || !is_array($data) || strtoupper((string) ($data['algorithm'] ?? '')) !== 'HMAC-SHA256')
		{
			return null;
		}

		return hash_equals(hash_hmac('sha256', $parts[1], $secret, true), $signature) ? $data : null;
	}
}
