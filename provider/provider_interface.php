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

/**
 * An identity provider the board can offer. Tag the service with
 * "slamb.externallogin.provider" to register a new one.
 */
interface provider_interface
{
	/**
	 * Short lowercase identifier, used in routes and config keys (max 20 characters).
	 */
	public function get_id(): string;

	/**
	 * Brand name shown on the buttons. Brand names are not translated.
	 */
	public function get_title(): string;

	/**
	 * Position in lists, lower comes first.
	 */
	public function get_sort_order(): int;

	/**
	 * Language key with a one-line instruction for creating the app in the developer console.
	 */
	public function get_help_lang_key(): string;

	/**
	 * Address of the developer console where the admin creates the app.
	 */
	public function get_console_url(): string;

	/**
	 * Address the visitor is sent to for signing in.
	 */
	public function get_authorize_url(string $client_id, string $redirect_uri, string $state, string $nonce, string $code_challenge): string;

	/**
	 * Exchange the authorisation code for the identity of the visitor.
	 *
	 * @throws provider_exception When the provider answer cannot be trusted
	 */
	public function fetch_identity(string $client_id, string $client_secret, string $redirect_uri, string $code, string $code_verifier, string $nonce): identity;
}
