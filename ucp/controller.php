<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\ucp;

use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use slamb\externallogin\service\account_repository;
use slamb\externallogin\service\logger;
use slamb\externallogin\service\provider_manager;

/**
 * The "Linked services" page of the user control panel.
 */
class controller
{
	public function __construct(
		protected language $language,
		protected request_interface $request,
		protected template $template,
		protected user $user,
		protected provider_manager $providers,
		protected account_repository $accounts,
		protected logger $logger
	)
	{
	}

	protected string $u_action = '';

	public function set_page_url(string $u_action): void
	{
		$this->u_action = $u_action;
	}

	public function display_options(): void
	{
		$user_id = (int) $this->user->data['user_id'];
		$unlink = $this->request->variable('unlink', '');

		if ($unlink !== '')
		{
			$this->unlink($user_id, $unlink);
		}

		$links = [];
		foreach ($this->accounts->get_links($user_id) as $row)
		{
			$links[$row['provider']] = $row;
		}

		foreach ($this->providers->all() as $id => $provider)
		{
			$linked = isset($links[$id]);

			// A service that is switched off can still be shown, so that it can be removed
			if (!$linked && !$this->providers->get_enabled($id))
			{
				continue;
			}

			$this->template->assign_block_vars('services', [
				'TITLE'		=> $provider->get_title(),
				'S_LINKED'	=> $linked,
				'LINKED'	=> $linked ? $this->user->format_date((int) $links[$id]['linked_time']) : '',
				'U_LINK'	=> $linked ? '' : $this->providers->get_link_url($id),
				'U_UNLINK'	=> $linked ? $this->u_action . '&amp;unlink=' . $id : '',
			]);
		}
	}

	protected function unlink(int $user_id, string $provider_id): void
	{
		$provider = $this->providers->all()[$provider_id] ?? null;
		$links = $this->accounts->get_links($user_id);

		if ($provider === null || !array_filter($links, static fn(array $row): bool => $row['provider'] === $provider_id))
		{
			return;
		}

		// Without a password the last service is the only way in
		if ((string) $this->user->data['user_password'] === '' && count($links) < 2)
		{
			trigger_error($this->language->lang('SLAMB_EXTLOGIN_ERR_LAST_LINK') . '<br /><br /><a href="' . $this->u_action . '">' . $this->language->lang('SLAMB_EXTLOGIN_BACK') . '</a>', E_USER_WARNING);
		}

		if (confirm_box(true))
		{
			$this->accounts->unlink_user($user_id, $provider_id);
			$this->logger->unlinked($user_id, $provider_id);

			meta_refresh(3, $this->u_action);
			trigger_error($this->language->lang('SLAMB_EXTLOGIN_UNLINKED', $provider->get_title()) . '<br /><br /><a href="' . $this->u_action . '">' . $this->language->lang('SLAMB_EXTLOGIN_BACK') . '</a>');
		}

		confirm_box(false, $this->language->lang('SLAMB_EXTLOGIN_UNLINK_CONFIRM', $provider->get_title()), build_hidden_fields(['unlink' => $provider_id]));
	}
}
