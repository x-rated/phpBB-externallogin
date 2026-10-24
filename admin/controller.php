<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\admin;

use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use slamb\externallogin\service\credentials;
use slamb\externallogin\service\provider_manager;

/**
 * The settings page in the ACP.
 */
class controller
{
	protected const FORM_KEY = 'slamb_externallogin_acp';

	protected string $u_action = '';

	public function __construct(
		protected language $language,
		protected request_interface $request,
		protected template $template,
		protected provider_manager $providers,
		protected credentials $credentials
	)
	{
	}

	public function set_page_url(string $u_action): void
	{
		$this->u_action = $u_action;
	}

	public function display_options(): void
	{
		add_form_key(self::FORM_KEY);

		$errors = [];
		$submitted = [];

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key(self::FORM_KEY))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			foreach ($this->providers->all() as $id => $provider)
			{
				$enabled = (bool) $this->request->variable($id . '_enabled', 0);
				$client_id = trim($this->request->variable($id . '_id', ''));
				$secret = trim($this->request->variable($id . '_secret', ''));

				// An empty field keeps the stored secret
				$secret = $secret !== '' ? $secret : null;

				if ($enabled && ($client_id === '' || ($secret === null && !$this->credentials->has_secret($id))))
				{
					$errors[] = $this->language->lang('ACP_SLAMB_EXTLOGIN_ERR_CREDENTIALS', $provider->get_title());
				}

				$submitted[$id] = [$enabled, $client_id, $secret];
			}

			if (!$errors)
			{
				foreach ($submitted as $id => [$enabled, $client_id, $secret])
				{
					$this->credentials->save($id, $enabled, $client_id, $secret);
				}

				trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
			}
		}

		foreach ($this->providers->all() as $id => $provider)
		{
			// After a failed save the visitor's input comes back instead of the stored values
			[$enabled, $client_id] = $submitted[$id] ?? [$this->credentials->is_switched_on($id), $this->credentials->get_client_id($id)];

			$this->template->assign_block_vars('providers', [
				'ID'			=> $id,
				'TITLE'			=> $provider->get_title(),
				'HELP'			=> $this->language->lang($provider->get_help_lang_key()),
				'U_CONSOLE'		=> $provider->get_console_url(),
				'REDIRECT_URI'	=> utf8_htmlspecialchars($this->providers->get_redirect_uri($id)),
				'CLIENT_ID'		=> utf8_htmlspecialchars($client_id),
				'DELETION_URI'	=> $id === 'facebook' ? utf8_htmlspecialchars($this->providers->get_facebook_deletion_url()) : '',
				'S_ENABLED'		=> $enabled,
				'S_HAS_SECRET'	=> $this->credentials->has_secret($id),
			]);
		}

		$this->template->assign_vars([
			'S_ERROR'	=> (bool) $errors,
			'ERROR_MSG'	=> implode('<br />', $errors),
			'U_ACTION'	=> $this->u_action,
		]);
	}
}
