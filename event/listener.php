<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\event;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use slamb\externallogin\service\provider_manager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Puts the provider buttons on the login box and on the registration page.
 */
class listener implements EventSubscriberInterface
{
	public function __construct(
		protected config $config,
		protected language $language,
		protected template $template,
		protected user $user,
		protected provider_manager $providers
	)
	{
	}

	public static function getSubscribedEvents(): array
	{
		return [
			'core.login_box_modify_template_data'	=> 'login_box',
			'core.ucp_register_data_before'			=> 'register_page',
		];
	}

	public function login_box($event): void
	{
		if ($event['admin'])
		{
			return;
		}

		$data = $event['login_box_template_data'];

		// Leave the board alone when it uses core OAuth as its login method
		if (!empty($data['PROVIDER_TEMPLATE_FILE']))
		{
			return;
		}

		$redirect = (string) $event['redirect'];

		if ($this->assign_buttons($redirect))
		{
			$data['PROVIDER_TEMPLATE_FILE'] = '@slamb_externallogin/login_buttons.html';
			$event['login_box_template_data'] = $data;
		}
	}

	public function register_page($event): void
	{
		if ($this->assign_buttons(''))
		{
			// The block goes below the whole form: its submit button belongs to the local registration
			$this->template->assign_var('S_SLAMB_EXTLOGIN_REGISTER', true);
		}
	}

	protected function assign_buttons(string $redirect): bool
	{
		$enabled = $this->providers->get_enabled_all();

		if (!$enabled || $this->user->data['is_registered'])
		{
			return false;
		}

		$this->language->add_lang('common', 'slamb/externallogin');

		foreach ($enabled as $id => $provider)
		{
			$this->template->assign_block_vars('slamb_externallogin_providers', [
				'ID'	=> $id,
				'TITLE'	=> $provider->get_title(),
				'URL'	=> $this->providers->get_start_url($id, $redirect),
			]);
		}

		return true;
	}
}
