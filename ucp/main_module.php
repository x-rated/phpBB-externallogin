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

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$language = $phpbb_container->get('language');
		$language->add_lang('common', 'slamb/externallogin');

		$this->tpl_name = 'ucp_externallogin_services';
		$this->page_title = $language->lang('UCP_SLAMB_EXTLOGIN_TITLE');

		/** @var \slamb\externallogin\ucp\controller $controller */
		$controller = $phpbb_container->get('slamb.externallogin.ucp.controller');
		$controller->set_page_url($this->u_action);
		$controller->display_options();
	}
}
