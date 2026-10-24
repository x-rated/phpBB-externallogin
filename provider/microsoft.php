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

/**
 * Microsoft identity platform: personal, work and school accounts.
 */
class microsoft extends oidc_provider
{
	protected const AUTHORITY = 'https://login.microsoftonline.com/';

	public function get_id(): string
	{
		return 'microsoft';
	}

	public function get_title(): string
	{
		return 'Microsoft';
	}

	public function get_sort_order(): int
	{
		return 20;
	}

	public function get_help_lang_key(): string
	{
		return 'ACP_SLAMB_EXTLOGIN_HELP_MICROSOFT';
	}

	public function get_console_url(): string
	{
		return 'https://entra.microsoft.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade';
	}

	protected function get_authorization_endpoint(): string
	{
		return self::AUTHORITY . 'common/oauth2/v2.0/authorize';
	}

	protected function get_token_endpoint(): string
	{
		return self::AUTHORITY . 'common/oauth2/v2.0/token';
	}

	protected function get_scopes(): string
	{
		return 'openid email profile';
	}

	protected function get_extra_authorize_params(): array
	{
		return ['prompt' => 'select_account'];
	}

	protected function is_valid_issuer(string $issuer, array $claims): bool
	{
		// The issuer contains the tenant of the account, which has to agree with the "tid" claim
		return isset($claims['tid']) && is_string($claims['tid'])
			&& preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $claims['tid'])
			&& hash_equals(self::AUTHORITY . strtolower($claims['tid']) . '/v2.0', strtolower($issuer));
	}

	// An e-mail address in a Microsoft token is never treated as verified (it can be set freely
	// in some tenants), so it is only offered as a suggestion on the sign-up form.
}
