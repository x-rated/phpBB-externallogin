<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\exception;

/**
 * Thrown when an identity provider answers with something we cannot trust.
 *
 * The message is meant for the server log only. Visitors get a generic text.
 */
class provider_exception extends \RuntimeException
{
}
