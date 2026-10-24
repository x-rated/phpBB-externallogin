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
 * Who the identity provider says the visitor is.
 *
 * The account is always bound to provider + uid. The e-mail address is only a hint
 * and counts as confirmed only when the provider itself says so.
 */
class identity
{
	public function __construct(
		public readonly string $provider,
		public readonly string $uid,
		public readonly string $email,
		public readonly bool $email_verified,
		public readonly string $name
	)
	{
	}
}
