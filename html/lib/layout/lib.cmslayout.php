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

class CmsLayout {

	/**
	 * Return the link for the admin section
	 * @return <type>
	 */
	public static function admin() {
		$res = '';
		$level = Docebo::user()->getUserLevelId();
		if ($level == ADMIN_GROUP_GODADMIN || $level == ADMIN_GROUP_ADMIN) {

			$res .= '<a href="'.Get::rel_path('adm').'">'
				.Get::img('standard/goto_admin.gif', Lang::t('_GOTO_ADMIN'))
				.Lang::t('_GOTO_ADMIN')
				.'</a>';
		}
		return $res;
	}

	/**
	 * Return the link for the logout
	 * @return <type>
	 */
	public static function logout() {
		$res="";
		if(!Docebo::user()->isAnonymous()) {

			$res .= '<a href="index.php?action=logout">'
				.Get::img('standard/exit.png', Lang::t('_LOG_LOGOUT'))
				.Lang::t('_LOG_LOGOUT')
				.'</a>';
		}

		return $res;
	}

	public static function mod_rewrite() {

		if (Get::cfg('use_mod_rewrite', '') == "on") {

			$base = Get::sett('url');
			if (preg_match("/127.0.0.1/", $base)) {
				$base = preg_replace("/127.0.0.1[^\d\\/][:]?([^\\/]*)/", $_SERVER["HTTP_HOST"], $base);
			}
			if (preg_match("/".$_SERVER["HTTP_HOST"]."/", $base)) {
				
				return '<base href="'.$base.'" />'."\n";
			}
		}
	}

	public static function navigation() {
		
		return navigatorArea(getid_page());
	}

	public static function banner() {

		return load_banner();
	}

	public static function languages() {

		return '';
	}

	public static function menuover() {

		return loadMenuOver();
	}

}
