<?php
/**
 *
 * External login. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Slamb, https://www.slamb.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace slamb\externallogin\acp;

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \slamb\externallogin\admin\controller $controller */
		$controller = $phpbb_container->get('slamb.externallogin.admin.controller');

		$this->tpl_name = 'acp_externallogin_settings';
		$this->page_title = $phpbb_container->get('language')->lang('ACP_SLAMB_EXTLOGIN_SETTINGS');

		$controller->set_page_url($this->u_action);
		$controller->display_options();
	}
}
