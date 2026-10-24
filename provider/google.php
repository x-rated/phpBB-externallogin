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

class google extends oidc_provider
{
	public function get_id(): string
	{
		return 'google';
	}

	public function get_title(): string
	{
		return 'Google';
	}

	public function get_sort_order(): int
	{
		return 10;
	}

	public function get_help_lang_key(): string
	{
		return 'ACP_SLAMB_EXTLOGIN_HELP_GOOGLE';
	}

	public function get_console_url(): string
	{
		return 'https://console.cloud.google.com/auth/clients';
	}

	protected function get_authorization_endpoint(): string
	{
		return 'https://accounts.google.com/o/oauth2/v2/auth';
	}

	protected function get_token_endpoint(): string
	{
		return 'https://oauth2.googleapis.com/token';
	}

	protected function get_scopes(): string
	{
		return 'openid email profile';
	}

	protected function get_extra_authorize_params(): array
	{
		// Let the visitor pick the account instead of silently using the one signed in
		return ['prompt' => 'select_account'];
	}

	protected function is_valid_issuer(string $issuer, array $claims): bool
	{
		return in_array($issuer, ['https://accounts.google.com', 'accounts.google.com'], true);
	}

	protected function is_email_verified(array $claims): bool
	{
		// Google sends a boolean, older tokens a string
		return isset($claims['email_verified']) && filter_var($claims['email_verified'], FILTER_VALIDATE_BOOLEAN);
	}
}
