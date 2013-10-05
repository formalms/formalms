<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * @package admin-core
 * @subpackage dashboard
 */

class Dashboard_Framework extends Dashboard {

	function Dashboard_Framework() {

	}

	function getBoxContent() {

		$html = array();
		if(!checkPerm('view_org_chart', true, 'directory', 'framework')) return $html;

		require_once(_base_.'/lib/lib.userselector.php');
		$user_dir = new UserSelector();
		$user_stats = $user_dir->getUsersStats();

		$lang =& DoceboLanguage::createInstance('dashboard', 'framework');

		if(Get::sett('welcome_use_feed') == 'on') {

			require_once(_base_.'/lib/lib.fsock_wrapper.php');
			$fp = new Fsock();
			$released_version = $fp->send_request('http://www.formalms.org/versions/release.txt');

			if(!$fp) {

				$released_version = '<strong class="old_release">'.$lang->def('_UNKNOWN_RELEASE').'</strong>';
			} else {

				if($released_version == false) {

					$released_version = '<strong class="ok_release">'.$lang->def('_UNKNOWN_RELEASE').'</strong>';
				}
				if($released_version == Get::sett('core_version')) {
					$released_version = '<strong class="ok_release">'.$released_version.'</strong>';
				} else {
					$released_version = '<strong class="old_release">'.$released_version.' ('.$lang->def('_NEW_RELEASE_AVAILABLE').')</strong>';
				}
			}
		}
		$html[] = '<h2 class="inline">'.$lang->def('_USERS_PANEL').'</h2>'
			.'<p>'
				.$lang->def('_TOTAL_USER').': <b>'.($user_stats['all'] - 1).'</b>;<br />'
				.$lang->def('_SUSPENDED').': <b>'.$user_stats['suspended'].'</b>;<br />'
				.( checkPerm('approve_waiting_user', true, 'directory', 'framework')
					? $lang->def('_WAITING_USERS').': <b>'.$user_stats['waiting'].'</b>;'
					: '' )
			.'</p><p>'
				.$lang->def('_SUPERADMIN_USER').': <b>'.$user_stats['superadmin'].'</b>;<br />'
				.$lang->def('_ADMIN_USER').': <b>'.$user_stats['admin'].'</b>;<br />'
                .$lang->def('_PUBLIC_ADMIN_USER').': <b>'.$user_stats['public_admin'].'</b>;'
			.'</p><p>'
				.$lang->def('_REG_TODAY').': <b>'.$user_stats['register_today'].'</b>;<br />'
				.$lang->def('_REG_YESTERDAY').': <b>'.$user_stats['register_yesterday'].'</b>;<br />'
				.$lang->def('_REG_LASTSEVENDAYS').': <b>'.$user_stats['register_7d'].'</b>;'
			.'</p><p>'
				.$lang->def('_INACTIVE_USER').': <b>'.$user_stats['inactive_30d'].'</b>;<br />'
				.$lang->def('_ONLINE_USER').': <b>'.$user_stats['now_online'].'</b>;'
			.'</p><p>'
				.$lang->def('_CORE_VERSION').': <b>'.Get::sett('core_version').'</b>;<br />'
				.( Get::sett('welcome_use_feed') == 'on' ? $lang->def('_LAST_RELEASED').': '.$released_version.';' : '' )
			.'</p>';
		return $html;
	}

}

?>