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

if(!Docebo::user()->isAnonymous()) {
	YuiLib::load('base,menu');
	require_once(_lms_.'/lib/lib.middlearea.php');

	$ma = new Man_MiddleArea();

	$user_level = Docebo::user()->getUserLevelId();

	$query_menu = "
	SELECT mo.idModule, mo.module_name, mo.default_op, mo.mvc_path, mo.default_name, mo.token_associated, mo.module_info
	FROM ".$GLOBALS['prefix_lms']."_module AS mo
		JOIN ".$GLOBALS['prefix_lms']."_menucourse_under AS under
			ON ( mo.idModule = under.idModule)
	WHERE module_info IN ('all', 'user', 'public_admin')
	ORDER BY module_info, under.sequence ";

	$menu = array();
	$re_menu_voice = sql_query($query_menu);
	while(list($id_m, $module_name, $def_op, $mvc_path, $default_name, $token, $m_info) = sql_fetch_row($re_menu_voice)) {

		
        if($ma->currentCanAccessObj('mo_'.$id_m) && checkPerm($token, true, $module_name,  true)) {

            // if e-learning tab disabled, show classroom courses
            if ($module_name ==='course' && !$ma->currentCanAccessObj('tb_elearning'))
                $mvc_path = 'lms/classroom/show';
            
			$menu[$m_info][$id_m] = array(
				'index.php?'.( $mvc_path ? 'r='.$mvc_path : 'modname='.$module_name.'&amp;op='.$def_op ).'&amp;sop=unregistercourse',
				Lang::t($default_name, 'menu_over'),
				false
			);
		}
	}
	if(isset($menu['all'])) $menu_i = count($menu['all'])-1;
	else $menu_i = -1;
	$setup_menu = '';
	// Menu for the public admin
	/*if(!empty($menu['user'])) {
		$menu['all'][] = array(
			'#',
			Lang::t('_MY_AREA', 'menu_over'),
			'user'
		);
		$menu_i++;
	}*/

	// Menu for messages
	if($ma->currentCanAccessObj('mo_message')) {
		require_once($GLOBALS['where_framework'].'/lib/lib.message.php');
		$msg = new Man_Message();
		$unread_num = $msg->getCountUnreaded(getLogUserId(), array(), '', true);
		$menu['all'][] = array(
			'index.php?modname=message&amp;op=message&amp;sop=unregistercourse',
			Lang::t('_MESSAGES', 'menu_over').( $unread_num ? ' <b class="num_notify">'.$unread_num.'</b>' : '' ),
			false
		);
		$menu_i++;
	}

	// Customer help
	if ($ma->currentCanAccessObj('mo_help')) {

		$help_email = trim( Get::sett('customer_help_email', '') );
		$can_send_emails = !empty( $help_email ) ? true : false;
		$can_admin_settings = checkRole('/framework/admin/setting/view', true);

		if ($can_send_emails) {

			cout(Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true), 'scripts');
			cout(Util::get_js(Get::rel_path('lms').'/modules/customer_help/customer_help.js', true), 'scripts');

			cout('<script type="text/javascript">'.
				' var CUSTOMER_HELP_AJAX_URL = "ajax.server.php?mn=customer_help&plf=lms&op=getdialog"; '.
				' var ICON_LOADING = "'.Get::tmpl_path().'images/standard/loadbar.gif"; '.
				' var LANG = new LanguageManager({'.
				'	_CONFIRM: "'.Lang::t('_CONFIRM').'",'.
				'	_UNDO: "'.Lang::t('_UNDO').'",'.
				'	_COURSE_NAME: "'.Lang::t('_COURSE_NAME', 'course').'",'.
				'	_VAL_COURSE_NAME: "'.(isset($GLOBALS['course_descriptor']) ? $GLOBALS['course_descriptor']->getValue('name') : "").'",'.
				'	_DLG_TITLE: "'.Lang::t('_CUSTOMER_HELP', 'customer_help').'",'.
				'	_LOADING: "'.Lang::t('_LOADING').'"'.
				'}); '
				.'</script>'
			, 'scripts');

			$menu['all'][] = array(
				'#',
				Lang::t('_CUSTOMER_HELP', 'customer_help'),
				false
			);
			$customer_help = ++$menu_i;
			$setup_menu .= " oMenuBar.getItem($customer_help).subscribe('click', CustomerHelpShowPopUp);";

		} else {

			if ($can_admin_settings) {
				$menu['all'][] = array(
					'../appCore/index.php?r=adm/setting/show',
					'<i>('.Lang::t('_CUSTOMER_HELP', 'customer_help').': '.Lang::t('_SET', 'standard').')</i>',
					false
				);
			}

		}
	}

	// Menu for the public admin
	if($user_level == ADMIN_GROUP_PUBLICADMIN && !empty($menu['public_admin'])) {
		$menu['all'][] = array(
			'#',
			Lang::t('_PUBLIC_ADMIN_AREA', 'menu_over'),
			'public_admin'
		);
		$menu_i++;
	}

	// Link for the administration
	if($user_level == ADMIN_GROUP_GODADMIN || $user_level == ADMIN_GROUP_ADMIN ) {
		$menu['all'][] = array(
			Get::rel_path('adm'),
			Lang::t('_GO_TO_FRAMEWORK', 'menu_over'),
			false
		);
		$menu_i++;
	}

	// print menu code -------------------------------------------------------------------------------------------------
	cout('<div id="lms_menu_container" class="lms_menu_over yuimenubar yuimenubarnav">'
		.'<div class="bd"><ul class="first-of-type">', 'menu_over');
	while(list($id_m, $voice) = each($menu['all'])) {

			cout('<li class="yuimenuitem">'
				.'<a class="yuimenuitemlabel"  id="mo_'.$id_m.'" href="'.$voice[0].'">'
				.'<span class="admmenu">'.$voice[1].'</span>'
				.'</a>',
			'menu_over');
			if($voice[2] !== false) {

				cout('<div id="submenu_'.$id_m.'" class="yuimenu">'
					.'<div class="bd"><ul class="first-of-type">', 'menu_over');
				while(list($id_m, $s_voice) = each($menu[ $voice[2] ])) {

					cout('<li class="yuimenuitem">'
						.'<a class="yuimenuitemlabel" href="'.$s_voice[0].'"">'
						.'<span class="admmenu">'.$s_voice[1].'</span>'
						.'</a>'
						.'</li>', 'menu_over');
				}
				cout('</ul></div>'
					.'</div>', 'menu_over');
			}
			cout('</li>', 'menu_over');
	}
	cout('</ul></div>'
		.'</div>'
	, 'menu_over');

	cout('<script type="text/javascript">'."
	YAHOO.util.Event.onContentReady('lms_menu_container', function () {
		var oMenuBar = new YAHOO.widget.".( Lang::direction() == 'rtl' ? 'MenuBarRtl' : 'MenuBar' )."('lms_menu_container', {
			maxheight : 600, minscrollheight : 550, lazyload: true, effect: { effect: YAHOO.widget.ContainerEffect.FADE, duration: 0.25 }
		});
		oMenuBar.render();
		".$setup_menu."
		});".
		'</script>'
	, 'scripts');
}

?>