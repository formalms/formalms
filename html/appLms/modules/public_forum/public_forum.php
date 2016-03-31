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

if(Docebo::user()->isAnonymous()) die("You can't access");

require_once(_lms_.'/lib/lib.levels.php');

define('PUBLIC_FORUM_COURSE_ID', 0);
define('PUBLIC_FORUM_EDITION_ID', 0);
define('PUBLIC_FORUM_COURSE_NAME', 'Public Forum');

function loadUnreaded() {

	$id_course = PUBLIC_FORUM_COURSE_ID;

	if(!isset($_SESSION['unreaded_forum'][$id_course])) {

		unset($_SESSION['unreaded_forum']);
		//-find last access---------------------------------------------------------------
		$no_entry = false;
		$reLast = sql_query("SELECT UNIX_TIMESTAMP(lastenter)" .
							" FROM core_user" .
							" WHERE idst = '".getLogUserId()."'");
		if(mysql_num_rows($reLast)) {
			list($last_forum_access_time) = sql_fetch_row($reLast);
		} else {
			$last_forum_access_time = 0;
			$no_entry = true;
		}
		$unreaded = array();
		$reUnreaded = sql_query("
		SELECT t.idThread, t.idForum, m.generator, COUNT(m.idMessage)
		FROM ".$GLOBALS['prefix_lms']."_forumthread AS t JOIN ".$GLOBALS['prefix_lms']."_forummessage AS m
		WHERE t.idThread = m.idThread AND m.author <> '".getLogUserId()."' AND UNIX_TIMESTAMP(m.posted) >= '".$last_forum_access_time."'
		GROUP BY t.idThread, t.idForum, m.generator");

		while(list($id_thread, $id_forum, $is_generator, $how_much_mess) = sql_fetch_row($reUnreaded)) {

			if($is_generator) {

				if(isset($unreaded[$id_forum]['new_thread']))
					$unreaded[$id_forum][$id_thread] = 'new_thread';
				else
					$unreaded[$id_forum][$id_thread] = 'new_thread';
			} else {
				if(isset($unreaded[$id_forum][$id_thread]))
					$unreaded[$id_forum][$id_thread] += $how_much_mess;
				else
					$unreaded[$id_forum][$id_thread] = $how_much_mess;
			}
		}
		$_SESSION['unreaded_forum'][$id_course] = $unreaded;
		//-set as now the last forum access------------------------------------------------
		if($no_entry) {
			sql_query("
			INSERT INTO  ".$GLOBALS['prefix_lms']."_forum_timing
			SET last_access = NOW(),
				idUser = '".getLogUserId()."',
				idCourse = '".PUBLIC_FORUM_COURSE_ID."'");
		} else {
			sql_query("
			UPDATE ".$GLOBALS['prefix_lms']."_forum_timing
			SET  last_access = NOW()
			WHERE idUser = ".(int)getLogUserId()." AND idCourse = '".PUBLIC_FORUM_COURSE_ID."'");
		}
	}
}

function forum() {
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::CreateInstance('forum');

	$mod_perm 	= checkPerm('mod', true);
	$moderate 	= checkPerm('moderate', true);
	$add_perm	= checkPerm('add', true);
	$base_link 	= 'index.php?modname=public_forum&amp;op=forum';
	$acl_man 	=& Docebo::user()->getAclManager();

	// Find and set unreaded message
	loadUnreaded();

	$tb = new Table( Get::sett('visuItem'), '', $lang->def('_ELEFORUM'), 'list-forums mobile-reverse');
	$tb->initNavBar('ini', 'link');
	$tb->setLink($base_link);

	$ini = $tb->getSelectedElement();

	// Construct query for forum display
	if($mod_perm) {

		$query_view_forum = "
		SELECT f.idForum, f.title, f.description, f.num_thread, f.num_post, f.locked, f.emoticons
		FROM ".$GLOBALS['prefix_lms']."_forum AS f
		WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."'
		ORDER BY f.sequence
		LIMIT $ini, ".Get::sett('visuItem');

		$query_num_view = "
		SELECT COUNT(*) FROM ".$GLOBALS['prefix_lms']."_forum AS f
		WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."'";
	} else {

		$acl 	=& Docebo::user()->getAcl();
		$all_user_idst = $acl->getSTGroupsST(getLogUserId());
		$all_user_idst[] = getLogUserId();
		
		$query_view_forum = "
		SELECT DISTINCT f.idForum, f.title, f.description, f.num_thread, f.num_post, f.locked, f.emoticons
		FROM ".$GLOBALS['prefix_lms']."_forum AS f
		WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."'
		ORDER BY f.sequence ";

		$query_view_forum = "
		SELECT DISTINCT f.idForum, f.title, f.description, f.num_thread, f.num_post, f.locked, f.emoticons
		FROM ".$GLOBALS['prefix_lms']."_forum AS f
			LEFT JOIN ".$GLOBALS['prefix_lms']."_forum_access AS fa ON ( f.idForum = fa.idForum )
		WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND
			( fa.idMember IS NULL OR fa.idMember IN (".implode($all_user_idst, ',')." )  )
		ORDER BY f.sequence ";

		$query_num_view = "
		SELECT COUNT( DISTINCT f.idForum )
		FROM ".$GLOBALS['prefix_lms']."_forum AS f
		WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."'";

	}

	$re_forum = sql_query($query_view_forum);
	list($tot_forum) = sql_fetch_row(sql_query($query_num_view));
	
	$re_last_post = sql_query("
	SELECT f.idForum, m.idThread, m.posted, m.title, m.author
	FROM ".$GLOBALS['prefix_lms']."_forum AS f LEFT JOIN
		".$GLOBALS['prefix_lms']."_forummessage AS m ON ( f.last_post = m.idMessage )
	WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."'");
	while(list($idF_p, $idT_p, $posted, $title_p, $id_a) = sql_fetch_row($re_last_post)) {

		if($posted !== NULL) {
			$last_post[$idF_p]['info'] = Format::date($posted).'<br />'.substr(strip_tags($title_p), 0, 15).' ...';
			$last_post[$idF_p]['author'] = $id_a;
			$last_authors[] = $id_a;
		}
	}

	// find authors names
	if(isset($last_authors)) {
		$authors_names =& $acl_man->getUsers($last_authors);
	}


	// switch to one of the 2 visualization method
	if(Get::sett('forum_as_table') == 'on') {

		// show forum list in a table -----------------------------------------
		// table header
		$type_h = array(
			'image hidden-xs hidden-sm',
			'col-xs-12 col-sm-3 col-lg-2 image',
			'col-xs-12 col-sm-3 col-lg-2 forumTitle',
			'col-xs-12 col-sm-2 text-left',
			'col-xs-12 col-sm-3 text-left',
			'col-xs-12 col-sm-1 col-lg-3 image text-center clearfix'
		);

		if($mod_perm) {
			$type_h[] = 'image'; $type_h[] = 'image'; $type_h[] = 'image'; $type_h[] = 'image'; $type_h[] = 'image'; $type_h[] = 'image';
		}
		$tb->setColsStyle($type_h);

		// $cont_h = array(
		// 	'<img src="'.getPathImage('fw').'blank.png" title="'.$lang->def('_EMOTICONS').'" alt="'.$lang->def('_EMOTICONS').'" />',
		// 	$lang->def('_TITLE'),
		// 	$lang->def('_DESCRIPTION'),
		// 	$lang->def('_NUMTHREAD'),
		// 	$lang->def('_NUMPOST'),
		// 	$lang->def('_LASTPOST'),
		// 	'Azioni'
		// );
		// if($mod_perm) {
		// 	$cont_h[] = '<img src="'.getPathImage().'standard/down.png" title="'.$lang->def('_MOVE_DOWN').'" alt="'.$lang->def('_DOWN').'" />';
		// 	$cont_h[] = '<img src="'.getPathImage().'standard/up.png" title="'.$lang->def('_UP').'" alt="'.$lang->def('_UP').'" />';
		// 	$cont_h[] = '<img src="'.getPathImage().'standard/moduser.png" title="'.$lang->def('_VIEW_PERMISSION').'" alt="'.$lang->def('_VIEW_PERMISSION').'" />';
		// 	$cont_h[] = '<img src="'.getPathImage().'standard/download.png" title="'.$lang->def('_EXPORT_CSV').'" alt="'.$lang->def('_EXPORT_CSV').'" />';
		// 	$cont_h[] = '<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />';
		// 	$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" />';
		// }
		// $tb->addHead($cont_h);

		// table body
		$i = 1;
		while(list($idF, $title, $descr, $num_thread, $num_post, $locked, $emoticons) = sql_fetch_row($re_forum) ) {
			if (checkPublicForumPerm('view', $idF) || checkPerm('mod', true))
			{
				$c_css 			= '';
				$mess_notread 	= 0;
				$thread_notread = 0;
				// NOTES: status
				// if($locked)	$status = '<span class="ico-sprite subs_locked"><span>'.Lang::t('_LOCKED', 'forum').'</span></span>';
				if($locked)	$status = '<span class="glyphicon glyphicon-locked"></span>&nbsp;<span>'.Lang::t('_LOCKED', 'forum').'</span>';
				elseif( isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$idF])) {
	
					if(isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$idF]) && is_array($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$idF])) {
						foreach($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$idF] as $k => $n_mess)
							if($n_mess != 'new_thread') $mess_notread += $n_mess;
							else $thread_notread += 1;
					}
					if($mess_notread > 0 || $thread_notread > 0) {
						// $status = '<img src="'.getPathImage().'standard/msg_unread.png" alt="'.$lang->def('_UNREAD').'" />';
						$status = '<span class="glyphicon glyphicon-folder-close" title="'.$lang->def('_UNREAD').'"></span>';
						$c_css = ' class="text_bold"';
					} else {
						$status = '';
					}
				} else $status = '';
	
				if (strpos($emoticons, '.gif') !== false) {
					$emoticon_img ='<img src="'.getPathImage().'emoticons/'.$emoticons.'" title="'.$lang->def('_EMOTICONS').'" alt="'.$lang->def('_EMOTICONS').'" />';
				}
				else {
					$emoticon_img ='<span class="emoticon emo-'.$emoticons.'"><span></span></span>';
				}

				// NOTES: other content
				$content = array(
								$emoticon_img,
								// '<a'.$c_css.' href="index.php?modname=public_forum&amp;op=thread&amp;idForum='.$idF.'">'.$status.' '.$title.'</a>',
								'<a'.$c_css.' href="index.php?modname=public_forum&amp;op=thread&amp;idForum='.$idF.'"><strong>'.$status.' '.$title.'</strong></a>',
								$descr,
								'<p>Discussioni: <span class="badge">'.$num_thread.( $thread_notread ? '<div class="forum_notread">'.$thread_notread.' '.$lang->def('_ADD').'</div>' : '' ).'</span></p><p>Messaggi: <span class="badge">'.$num_post.( $mess_notread ? '<div class="forum_notread">'.$mess_notread.' '.$lang->def('_ADD').'</div>' : '' ).'</span></p>' );
				
				if(isset($last_post[$idF])) {
	
					$author = $last_post[$idF]['author'];
					$content[] = $last_post[$idF]['info'].' ( '.$lang->def('_BY').': <span class="mess_author">'
								.( isset($authors_names[$author])
									? ( $authors_names[$author][ACL_INFO_LASTNAME].$authors_names[$author][ACL_INFO_FIRSTNAME] == ''
											? $acl_man->relativeId($authors_names[$author][ACL_INFO_USERID])
											: $authors_names[$author][ACL_INFO_LASTNAME].' '.$authors_names[$author][ACL_INFO_FIRSTNAME] )
									: $lang->def('_UNKNOWN_AUTHOR')
								).'</span> )';
				} else {
	
					$content[] = $lang->def('_NONE');
				}
				// NOTES: mod and perm
				if($mod_perm) {
					// if($i != $tot_forum) $content[] = '<a href="index.php?modname=public_forum&amp;op=downforum&amp;idForum='.$idF.'">
					// 	<img src="'.getPathImage().'standard/down.png" title="'.$lang->def('_MOVE_DOWN').'" alt="'.$lang->def('_DOWN').'" /></a>';
					// else $content[] = '';
	
					// if($i != 1) $content[] = '<a href="index.php?modname=public_forum&amp;op=moveupforum&amp;idForum='.$idF.'">
					// 	<img src="'.getPathImage().'standard/up.png" title="'.$lang->def('_UP').'" alt="'.$lang->def('_UP').'" /></a>';
					// else $content[] = '';
	
					// $content[] = '<a href="index.php?modname=public_forum&amp;op=modforumaccess&amp;idForum='.$idF.'&amp;load=1">
					// 	<img src="'.getPathImage().'standard/moduser.png" title="'.$lang->def('_VIEW_PERMISSION').'" alt="'.$lang->def('_VIEW_PERMISSION').'" /></a>';
					// $content[] = '<a href="index.php?modname=public_forum&amp;op=export&amp;idForum='.$idF.'" ' .
					// 	'title="'.$lang->def('_EXPORTFORUM').' : '.strip_tags($title).'">
					// 	<img src="'.getPathImage().'standard/download.png" alt="'.$lang->def('_EXPORTFORUM').'" /></a>';
					// $content[] = '<a href="index.php?modname=public_forum&amp;op=modforum&amp;idForum='.$idF.'">
					// 	<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a>';
					// $content[] = '<a href="index.php?modname=public_forum&amp;op=delforum&amp;idForum='.$idF.'" title="'.$lang->def('_DEL').' : '.strip_tags($title).'">
					// 	<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').' : '.strip_tags($title).'" alt="'.$lang->def('_DEL').'" /></a>';

					$content[] = '<div class="btn-group visible-xs visible-lg">
													<a class="btn btn-default'.($i == $tot_forum ? ' disabled' : '').'" href="index.php?modname=public_forum&amp;op=downforum&amp;idForum='.$idF.'" title="'.$lang->def('_MOVE_DOWN').'">
														<span class="glyphicon glyphicon-chevron-down"></span>
													</a>
													<a class="btn btn-default'.($i == 1 ? ' disabled' : '').'" href="index.php?modname=public_forum&amp;op=moveupforum&amp;idForum='.$idF.'" title="'.$lang->def('_UP').'">
														<span class="glyphicon glyphicon-chevron-up"></span>
													</a>
													<a class="btn btn-default href="index.php?modname=public_forum&amp;op=modforumaccess&amp;idForum='.$idF.'&amp;load=1" title="'.$lang->def('_VIEW_PERMISSION').'">
														<span class="glyphicon glyphicon-ban-circle"></span>
													</a>
													<a class="btn btn-default href="index.php?modname=public_forum&amp;op=export&amp;idForum='.$idF.'" title="'.$lang->def('_EXPORTFORUM').' : '.strip_tags($title).'">
														<span class="glyphicon glyphicon-save"></span>
													</a>
													<a class="btn btn-default href="index.php?modname=public_forum&amp;op=modforum&amp;idForum='.$idF.'" title="'.$lang->def('_MOD').'">
														<span class="glyphicon glyphicon-pencil"></span>
													</a>
													<a class="btn btn-default href="index.php?modname=public_forum&amp;op=delforum&amp;idForum='.$idF.'" title="'.$lang->def('_DEL').' : '.strip_tags($title).'">
														<span class="glyphicon glyphicon-remove"></span>
													</a>
												</div>
												<div class="dropdown hidden-xs hidden-lg">
														<button class="btn btn-default dropdown-toggle" type="button" id="forumFunctions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
														  <span class="glyphicon glyphicon-cog"></span> &nbsp;<span class="caret"></span>
														</button>
														<ul class="dropdown-menu" aria-labelledby="forumFunctions">
															<li>
																<a class="'.($i == $tot_forum ? ' hidden' : '').'" href="index.php?modname=public_forum&amp;op=downforum&amp;idForum='.$idF.'">
																	<span class="glyphicon glyphicon-chevron-down"></span> Sposta giù
																</a>
															</li>
															<li>
																<a class="'.($i == 1 ? ' hidden' : '').'" href="index.php?modname=public_forum&amp;op=moveupforum&amp;idForum='.$idF.'">
																	<span class="glyphicon glyphicon-chevron-up"></span> Sposta su
																</a>
															</li>
															<li>
																<a href="index.php?modname=public_forum&amp;op=modforumaccess&amp;idForum='.$idF.'&amp;load=1">
																	<span class="glyphicon glyphicon-ban-circle"></span> Cambia permessi
																</a>
															</li>
															<li>
																<a href="index.php?modname=public_forum&amp;op=export&amp;idForum='.$idF.'">
																	<span class="glyphicon glyphicon-save"></span> Esporta su CSV
																</a>
															</li>
															<li>
																<a href="index.php?modname=public_forum&amp;op=modforum&amp;idForum='.$idF.'">
																	<span class="glyphicon glyphicon-pencil"></span> Modifica
																</a>
															</li>
															<li>
																<a href="index.php?modname=public_forum&amp;op=delforum&amp;idForum='.$idF.'">
																	<span class="glyphicon glyphicon-remove"></span> Elimina
																</a>
															</li>
														</ul>
												</div>';
				}
				$tb->addBody( $content );
				++$i;
			}
		}
		if($mod_perm) {

			// $tb->addActionAdd('<a class="ico-wt-sprite subs_add" href="index.php?modname=public_forum&amp;op=addforum"><span>'
			// 	.$lang->def('_ADDFORUM')
			// 	.'</span></a>');

			$tb->addActionAdd('<div class="btn-group col-lg-9 visible-lg">
													<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=addforum">
														<span class="glyphicon glyphicon-plus-sign"></span>
														<span>'.$lang->def('_ADDFORUM').'</span>
													</a>
												</div>
												<div class="dropdown col-xs-5 col-sm-4 hidden-lg pull-left">
														<button class="btn btn-default dropdown-toggle" type="button" id="tableActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
														  <span class="glyphicon glyphicon-cog"></span> &nbsp;
														  Operazioni &nbsp;
														  <span class="caret"></span>
														</button>
														<ul class="dropdown-menu dropdown-menu-left" aria-labelledby="tableActions">
															<li>
																<a href="index.php?modname=public_forum&amp;op=addforum">
																	<span class="glyphicon glyphicon-plus-sign"></span>
																	<span>'.$lang->def('_ADDFORUM').'</span>
																</a>
															</li>
														</ul>
												</div>
												<div class="col-xs-7 col-sm-6 col-md-4 col-lg-3 pull-right">'
												.Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search')
												.'<div class="input-group">'
												.Form::getSearchInputTextfield('', '', '', 'Cerca argomento...', '', '', '', '')
												.'<div class="input-group-btn"><button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-search"></span></button></div>'
												.'</div>'
												.Form::closeForm()
												.'</div>');
		}
		$GLOBALS['page']->add(
		 	getTitleArea($lang->def('_FORUM'), 'forum')
		// 	.'<div class="std_block">'
		// 	.'<div class="quick_search_form">'
		// 	.'<label for="search_arg">'.$lang->def('_SEARCH_LABEL').'</label> '
		// 	.Form::getInputTextfield(	'search_t',
		// 								'search_arg',
		// 								'search_arg',
		// 								'',
		// 								$lang->def('_SEARCH'), 255, '' )
		// 	.'<input class="search_b" type="submit" id="search_button" name="search_button" value="'.$lang->def('_SEARCH').'" />'
		// 	.'</div>'
		// 	.Form::closeForm()
		.$tb->getTable()
		, 'content');
	} else {

		// second view styles
		$i = 1;
		$GLOBALS['page']->add(
			getTitleArea($lang->def('_FORUM'), 'forum')
			.'<div class="std_block">'
			.Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search')
			.'<div class="quick_search_form">'
			.'<label for="search_arg">'.$lang->def('_SEARCH_LABEL').'</label> '
			.Form::getInputTextfield(	'search_t',
										'search_arg',
										'search_arg',
										'',
										$lang->def('_SEARCH'), 255, '' )
			.'<input class="search_b" type="submit" id="search_button" name="search_button" value="'.$lang->def('_SEARCH').'" />'
			.'</div>'
			.Form::closeForm()
			, 'content');
		while( list($idF, $title, $descr, $num_thread, $num_post, $locked, $emoticons) = sql_fetch_row( $re_forum ) ) {
			if (checkPublicForumPerm('view', $idF) || checkPerm('mod', true))
			{
			$c_css = '';
			$thread_notread = 0;
			$mess_notread = 0;
			// NOTES: status
			if($locked)	$status = '<span class="ico-sprite subs_locked"><span>'.Lang::t('_LOCKED', 'forum').'</span></span>';
			
			elseif( isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$idF])) {

				if(isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$idF]) && is_array($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$idF])) {
					foreach($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$idF] as $k => $n_mess)
						if($n_mess != 'new_thread') $mess_notread += $n_mess;
						else $thread_notread += 1;
				}
				if($mess_notread > 0 || $thread_notread > 0) {
					$status = '<img src="'.getPathImage().'standard/msg_unread.png" alt="'.$lang->def('_UNREAD').'" /> ';
					$c_css = ' class="text_bold"';
				} else {
					$status = '';
				}
			} else {
				$status = '';
			}

			$GLOBALS['page']->add(
				'<table class="forum_table" cellspacing="0" summary="'.$lang->def('_FORUM_INFORMATION').'">'
				.'<tr class="forum_header">'
					.'<th class="forum_title">'.$status.'&nbsp;'
					.'<img src="'.getPathImage('fw').'emoticons/'.$emoticons.'" title="'.$lang->def('_EMOTICONS').'" alt="'.$lang->def('_EMOTICONS').'" />'
					.'&nbsp;'
					.'<a'.$c_css.' href="index.php?modname=public_forum&amp;op=thread&amp;idForum='.$idF.'">'.$title.'</a>'
					.'</th>'
					.'<th class="image" nowrap="nowrap">'.$lang->def('_NUMTHREAD').'</th>'
					.'<th class="image" nowrap="nowrap">'.$lang->def('_NUMPOST').'</th>'
				.'</tr>'
				.'<tr>'
					.'<td>'.$descr.'</td>'
					.'<td class="image" nowrap="nowrap">'.$num_thread
						.( $thread_notread ? '<div class="forum_notread">'.$thread_notread.' '.$lang->def('_UNREAD').'</div>' : '' )
					.'</td>'
					.'<td class="image" nowrap="nowrap">'.$num_post
						.( $mess_notread ? '<div class="forum_notread">'.$mess_notread.' '.$lang->def('_UNREAD').'</div>' : '' )
					.'</td>'
				.'</tr>'
				.'<tr>'
					.'<td colspan="3">', 'content');

			if(isset($last_post[$idF])) {

				$author = $last_post[$idF]['author'];
				$GLOBALS['page']->add('<span class="forum_lastpost">'.$lang->def('_LASTPOST').' : '.$last_post[$idF]['info'].' ( '.$lang->def('_BY').': <span class="mess_author">'
					.( isset($authors_names[$author])
						? ( $authors_names[$author][ACL_INFO_LASTNAME].$authors_names[$author][ACL_INFO_FIRSTNAME] == ''
								? $acl_man->relativeId($authors_names[$author][ACL_INFO_USERID])
								: $authors_names[$author][ACL_INFO_LASTNAME].' '.$authors_names[$author][ACL_INFO_FIRSTNAME] )
						: $lang->def('_UNKNOWN_AUTHOR')
					).'</span> )'
					.'</span>'
				, 'content');

			} else {

				//$GLOBALS['page']->add($lang->def('_NONE'), 'content');
			}
			$GLOBALS['page']->add(
					'</td>'
				.'</tr>'
				.'<tr>'
					.'<td colspan="3" class="forum_manag">', 'content');
			if($mod_perm) {

				$GLOBALS['page']->add('<ul class="link_list_inline">', 'content');
				if($i != $tot_forum) {
					$GLOBALS['page']->add('<li><a href="index.php?modname=public_forum&amp;op=downforum&amp;idForum='.$idF.'">
					<img src="'.getPathImage().'standard/down.png" title="'.$lang->def('_MOVE_DOWN').'" alt="'.$lang->def('_DOWN').'" /></a></li>'
					, 'content');
				}
				if($i != 1) {
					$GLOBALS['page']->add('<li><a href="index.php?modname=public_forum&amp;op=moveupforum&amp;idForum='.$idF.'">
					<img src="'.getPathImage().'standard/up.png" title="'.$lang->def('_UP').'" alt="'.$lang->def('_UP').'" /></a></li>', 'content');
				} else {
					$GLOBALS['page']->add('<li><div style=" display: inline; margin: 0px 11px;"></div></li>', 'content');
				}
				$GLOBALS['page']->add(
					'<li><a href="index.php?modname=public_forum&amp;op=modforumaccess&amp;idForum='.$idF.'&amp;load=1">
						<img src="'.getPathImage().'standard/moduser.png" title="'.$lang->def('_VIEW_PERMISSION').'" alt="'.$lang->def('_VIEW_PERMISSION').'" /></a></li>'
					.'<li><a href="index.php?modname=public_forum&amp;op=export&amp;idForum='.$idF.'" title="'.$lang->def('_EXPORTFORUM').' : '.strip_tags($title).'">
						<img src="'.getPathImage().'standard/download.png" alt="'.$lang->def('_EXPORTFORUM').'" /></a></li>'
					.'<li><a href="index.php?modname=public_forum&amp;op=modforum&amp;idForum='.$idF.'">
						<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a></li>'
					.'<li><a href="index.php?modname=public_forum&amp;op=delforum&amp;idForum='.$idF.'" title="'.$lang->def('_DEL').' : '.strip_tags($title).'">
						<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').' : '.strip_tags($title).'" alt="'.$lang->def('_DEL').'" /></a></li>'
				, 'content');
				$GLOBALS['page']->add('</ul>', 'content');
			}
			$GLOBALS['page']->add('</td>'
				.'</tr>'
				.'</table>', 'content');
				$i++;
			}
		}

		if($add_perm) {
			$GLOBALS['page']->add(
				'<div class="table-container-below">'
				.'<a class="ico-wt-sprite subs_add" href="index.php?modname=public_forum&amp;op=addforum"><span>'
				.$lang->def('_ADDFORUM')
				.'</span></a>'
				.'</div>', 'content');
		}
		$GLOBALS['page']->add(
			$tb->getNavBar($ini, $tot_forum)
			.'</div>', 'content' );
	}
	if($mod_perm) {
					
		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=delforum]');
	}
}

//---------------------------------------------------------------------------//

function addforum() {
	checkPerm('add');

	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum');

	$default = 'blank';
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_FORUM'), 'forum')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=public_forum&amp;op=forum', $lang->def('_BACK'))
		.Form::openForm('addforumform', 'index.php?modname=public_forum&amp;op=insforum')
		.Form::openElementSpace()
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $lang->def('_NOTITLE'))
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $lang->def('_DESCRIPTION'))
		.Form::openFormLine()
		//.Form::getLabel('emoticons', $lang->def('_EMOTICONS'))
		//.'<select class="dropdown" id="emoticons" name="emoticons">'
		.'<div id="emoticon_menu_box">
		<input type="button" id="emoticon_btn" name="emoticon_btn" value="'.$lang->def('_EMOTICONS').'">
		</div>'
		.'<select class="dropdown" style="display: none;" id="emoticons" name="emoticons">'
	, 'content');

	/*$templ = dir(getPathImage('fw').'emoticons/');
	while($elem = $templ->read()) {

		if(strpos($elem, '.gif') !== false) {
			$GLOBALS['page']->add(
				'<option value="'.$elem.'" class="option_with_image" style="background-image: url(\''.getPathImage('fw').'emoticons/'.$elem.'\');"'
				.( $elem == $default ? ' selected="selected"' : '' )
				.'>'
				.$elem.'</option>'
			, 'content');
		}
	}
	closedir($templ->handle);*/

	Util::get_js(Get::rel_path('lms').'/modules/forum/forum.js', true, true);
	$emoticon_items ='';
	$emoticons_arr =getEmoticonsArr();

	foreach($emoticons_arr as $elem) {
		$emoticon_items.='<option value="'.$elem.'" '
			.( $elem == $default ? ' selected="selected"' : '' )
			.'>'.$elem.'</option>';
	}

	$GLOBALS['page']->add($emoticon_items, 'content');

	$GLOBALS['page']->add(
		'</select>'
		.'<select class="dropdown" id="emoticons_menu" name="emoticons">'.$emoticon_items.'</select>'
		.Form::closeFormLine()
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('insforum', 'insforum', $lang->def('_INSERT'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function insforum() {
	checkPerm('add');

	$lang =& DoceboLanguage::createInstance('forum');

	if(isset($_POST['undo'])) Util::jump_to('index.php?modname=public_forum&op=forum');
	if($_POST['title'] == '') {
		$_POST['title'] = $lang->def('_NOTITLE');
	}

	// finding sequence
	list($seq) = sql_fetch_row(sql_query("
	SELECT MAX(sequence) + 1
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."'"));

	$ins_query = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_forum
	( idCourse, title, description, sequence, emoticons ) VALUES
	( '".(int)PUBLIC_FORUM_COURSE_ID."',
		'".$_POST['title']."',
		'".$_POST['description']."',
		'$seq',
		'".$_POST['emoticons']."' )";
	if(!sql_query( $ins_query )) Util::jump_to('index.php?modname=public_forum&op=forum&result=err');

	if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
	
		list($idForum) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		$id_user = getLogUserId();
		$perm = array();
		
		$perm['view'] = array($id_user);
		$perm['write'] = array($id_user);
		$perm['upload'] = array($id_user);
		$perm['moderate'] = array($id_user);
		
		saveForumPerm($idForum, $perm, array());
		
		Docebo::user()->loadUserSectionST();
		Docebo::user()->SaveInSession();
	}
	$recipients = '';
	if(!empty($recipients)) {

		require_once(_base_.'/lib/lib.eventmanager.php');



		$msg_composer = new EventMessageComposer();

		$msg_composer->setSubjectLangText('email', '_NEW_FORUM', false);
		$msg_composer->setBodyLangText('email', '_NEW_FORUM_BODY', array(	'[url]' => Get::sett('url'),
																			'[course]' => PUBLIC_FORUM_COURSE_NAME,
																			'[title]' => $_POST['title'],
																			'[text]' => $_POST['description'] ) );

		$msg_composer->setBodyLangText('sms', '_NEW_FORUM_BODY_SMS', array(	'[url]' => Get::sett('url'),
																			'[course]' => PUBLIC_FORUM_COURSE_NAME,
																			'[title]' => $_POST['title'],
																			'[text]' => $_POST['description'] ) );

		createNewAlert(		'ForumNewCategory',
							'forum',
							'addforum',
							1,
							$lang->def('_NEW_FORUM'),
							$recipients,
							$msg_composer );

	}

	Util::jump_to('index.php?modname=public_forum&op=forum&result=ok');
}

//---------------------------------------------------------------------------//

function modforum() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum');

	list($title, $text, $emoticons) = sql_fetch_row(sql_query("
	SELECT title, description, emoticons
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".(int)$_GET['idForum']."'"));

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_FORUM'), 'forum', $lang->def('_FORUM'))
		.'<div class="std_block">'
		.Form::openForm('addforumform', 'index.php?modname=public_forum&amp;op=upforum')
		.Form::openElementSpace()
		.Form::getHidden('idForum', 'idForum', (int)$_GET['idForum'])
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $title)
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $text)
		.Form::openFormLine()
		//.Form::getLabel('emoticons', $lang->def('_EMOTICONS'))
		//.'<select id="emoticons" name="emoticons">'
		.'<div id="emoticon_menu_box">
		<input type="button" id="emoticon_btn" name="emoticon_btn" value="'.$lang->def('_EMOTICONS').'">
		</div>'
		.'<select class="dropdown" style="display: none;" id="emoticons" name="emoticons">'
	, 'content');

	/* $templ = dir(getPathImage('fw').'emoticons/');
	while($elem = $templ->read()) {

		if(strpos($elem, '.gif') !== false) {
			$GLOBALS['page']->add(
				'<option value="'.$elem.'" class="option_with_image" style="background-image: url(\''.getPathImage('fw').'emoticons/'.$elem.'\');"'
				.( $elem == $emoticons ? ' selected="selected"' : '' )
				.'>'
				.$elem.'</option>'
			, 'content');
		}
	}
	closedir($templ->handle); */

	Util::get_js(Get::rel_path('lms').'/modules/forum/forum.js', true, true);
	$emoticon_items ='';
	$emoticons_arr =getEmoticonsArr();

	foreach($emoticons_arr as $elem) {
		$emoticon_items.='<option value="'.$elem.'" '
			.( $elem == $emoticons ? ' selected="selected"' : '' )
			.'>'.$elem.'</option>';
	}

	$GLOBALS['page']->add($emoticon_items, 'content');

	$GLOBALS['page']->add(
		'</select>'
		.'<select class="dropdown" id="emoticons_menu" name="emoticons">'.$emoticon_items.'</select>'
		.Form::closeFormLine()
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('insforum', 'insforum', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function upforum() {
	checkPerm('mod');

	if(isset($_POST['undo'])) Util::jump_to('index.php?modname=public_forum&op=forum');
	if($_POST['title'] == '') $_POST['title'] = Lang::t('_NOTITLE', 'forum');

	$ins_query = "
	UPDATE ".$GLOBALS['prefix_lms']."_forum
	SET title = '".$_POST['title']."',
		description = '".$_POST['description']."',
		emoticons = '".$_POST['emoticons']."'
	WHERE idForum = '".(int)$_POST['idForum']."'AND idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."'";
	if(!sql_query( $ins_query )) Util::jump_to('index.php?modname=public_forum&op=forum&result=err');
	Util::jump_to('index.php?modname=public_forum&op=forum&result=ok');
}

function moveforum($idForum, $direction) {
	checkPerm('mod');

	list( $seq ) = sql_fetch_row(sql_query("
	SELECT sequence
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".(int)$idForum."'"));

	if($direction == 'up') {
		//move up
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forum
		SET sequence = '$seq'
		WHERE idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND sequence = '".($seq - 1)."'");
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forum
		SET sequence = sequence - 1
		WHERE idCourse = '".PUBLIC_FORUM_COURSE_ID."' AND idForum = '".(int)$idForum."'");
	}
	if($direction == 'down') {
		//move down
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forum
		SET sequence = '$seq'
		WHERE idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND sequence = '".($seq + 1)."'");
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forum
		SET sequence = sequence + 1
		WHERE idCourse = '".PUBLIC_FORUM_COURSE_ID."' AND idForum = '".(int)$idForum."'");
	}
	Util::jump_to('index.php?modname=public_forum&op=forum');
}

function changestatus() {
	checkPerm('mod');

	list( $lock ) = sql_fetch_row(sql_query("
	SELECT locked
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".(int)$_GET['idForum']."'"));

	if($lock == 1) $new_status = 0;
	else $new_status = 1;

	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forum
	SET locked = '$new_status'
	WHERE idCourse = '".PUBLIC_FORUM_COURSE_ID."' AND idForum = '".(int)$_GET['idForum']."'");
	Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.(int)$_GET['idForum']);
}

//---------------------------------------------------------------------------//

function delforum() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum');
	$id_forum = importVar('idForum', true, 0);

	list($title, $text, $seq) = sql_fetch_row(sql_query("
	SELECT title, description, sequence
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".$id_forum."'"));

	if(isset($_POST['undo'])) Util::jump_to('index.php?modname=public_forum&op=forum');
	if(isset($_GET['confirm'])) {

		$re_thread = sql_query("
		SELECT idThread
		FROM ".$GLOBALS['prefix_lms']."_forumthread
		WHERE idForum = '".(int)$_GET['idForum']."'");
		while(list($idT) = sql_fetch_row($re_thread)) {

			if(!sql_query("
			DELETE FROM ".$GLOBALS['prefix_lms']."_forummessage
			WHERE idThread = '$idT'")) Util::jump_to('index.php?modname=public_forum&op=forum&result=err_del');
			unsetNotify('thread', $idT);
		}
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_forumthread
		WHERE idForum= '".$id_forum."'")) Util::jump_to('index.php?modname=public_forum&op=forum&result=err_del');
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_forum_access
		WHERE idForum='".$id_forum."'")) Util::jump_to('index.php?modname=public_forum&op=forum&result=err_del');
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_forum
		WHERE idForum='".$id_forum."'")) Util::jump_to('index.php?modname=public_forum&op=forum&result=err_del');

		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forum
		SET sequence = sequence - 1
		WHERE idForum = '".$id_forum."' AND sequence > '".$seq."'");

		unsetNotify('forum', $id_forum);
		Util::jump_to('index.php?modname=public_forum&op=forum&result=ok');
	} else {

		$GLOBALS['page']->add(
			getTitleArea($lang->def('_FORUM'), 'forum', $lang->def('_FORUM'))
			.'<div class="std_block">'
			.getDeleteUi($lang->def('_AREYOUSURE'),
				'<span class="text_bold">'.$lang->def('_TITLE').' :</span> '.$title.'<br />'
				.'<span class="text_bold">'.$lang->def('_DESCRIPTION').' :</span> '.$text,
				true,
				'index.php?modname=public_forum&amp;op=delforum&amp;idForum='.$_GET['idForum'].'&amp;confirm=1',
				'index.php?modname=public_forum&amp;op=forum' )
			.'</div>', 'content');
	}
}

//---------------------------------------------------------------------------//


function modforumaccess() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.userselector.php');
	$lang =& DoceboLanguage::createInstance('forum', 'lms');
	$out =& $GLOBALS['page'];
	$id_forum = importVar('idForum', true, 0);

	$aclManager = new DoceboACLManager();
	$user_select = new UserSelector();
	$user_select->show_user_selector = TRUE;
	$user_select->show_group_selector = TRUE;
	$user_select->show_orgchart_selector = TRUE;
	$user_select->show_fncrole_selector = FALSE;

	$user_select->nFields = 0;

	if(isset($_POST['cancelselector'])) Util::jump_to('index.php?modname=public_forum&amp;op=forum');
	if(isset($_POST['okselector'])) {

		$user_selected 	= $user_select->getSelection($_POST);

		$query_reader = "
		SELECT idMember
		FROM ".$GLOBALS['prefix_lms']."_forum_access
		WHERE idForum = '".$id_forum."'";
		$re_reader = sql_query($query_reader);
		$old_users = array();
		while(list($id_user) = sql_fetch_row($re_reader)) {

			$old_users[] = $id_user;
		}
		$add_reader = array_diff($user_selected, $old_users);
		$del_reader = array_diff($old_users, $user_selected);

		if(is_array($add_reader)) {

			while(list(, $idst) = each($add_reader)) {

				$query_insert = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_forum_access
				( idForum, idMember ) VALUES
				( 	'".$id_forum."',
					'".$idst."' )";
				sql_query($query_insert);
			}
		}
		if(is_array($del_reader)) {

			while(list(, $idst) = each($del_reader)) {

				$query_delete = "
				DELETE FROM ".$GLOBALS['prefix_lms']."_forum_access
				WHERE idForum = '".$id_forum."' AND idMember = '".$idst."'";
				sql_query($query_delete);
			}
		}
		Util::jump_to('index.php?modname=public_forum&amp;op=forum&amp;result=ok');
	}

	if(isset($_GET['load'])) {

		$query_reader = "
		SELECT idMember
		FROM ".$GLOBALS['prefix_lms']."_forum_access
		WHERE idForum = '".$id_forum."'";
		$re_reader = sql_query($query_reader);
		$users = array();
		while(list($id_user) = sql_fetch_row($re_reader)) {

			$users[$id_user] = $id_user;
		}
		$user_select->resetSelection($users);
	}
	$query_forum_name = "SELECT f.title
	FROM ".$GLOBALS['prefix_lms']."_forum AS f
	WHERE f.idCourse = ".(int)$_SESSION['idCourse']."
		AND f.idForum = ".(int)$id_forum." ";
	$row = sql_fetch_row(sql_query($query_forum_name));
	$forum_name = $row[0];
	$arr_idstGroup = $aclManager->getGroupsIdstFromBasePath('/lms/course/'.(int)$_SESSION['idCourse'].'/subscribed/');
	$user_select->setUserFilter('group',$arr_idstGroup);
	$user_select->setGroupFilter('path', '/lms/course/'.$_SESSION['idCourse'].'/group');

	cout( getTitleArea(array(
			'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
			$lang->def('_FORUM_ACCESS').' "'.$forum_name.'" '.$lang->def('_TO').''
		), 'forum')
		.'<div class="std_block">'
		, 'content');
	$user_select->loadSelector('index.php?modname=public_forum&amp;op=modforumaccess&amp;idForum='.$id_forum,
			'',
			$lang->def('_CHOOSE_FORUM_ACCESS'),
			true );
	cout('</div>', 'content');
}

function modforumaccess_old() {
	checkPerm('mod');

	require_once(_adm_."/lib/lib.simplesel.php");

	$out =& $GLOBALS['page'];
	$out->setWorkingZone("content");
	
	$lang =& DoceboLanguage::createInstance('public_forum', 'lms');

	$idForum = (int)importVar("idForum");


	$ssel = new SimpleSelector(true, $lang);

	$perm = array();
	$perm["view"]["img"] = getPathImage()."standard/view.png";
	$perm["view"]["alt"] = $lang->def("_VIEW");
	$perm["write"]["img"] = getPathImage()."standard/reply.png";
	$perm["write"]["alt"] = $lang->def("_ADD");
	$perm["upload"]["img"] = getPathImage()."standard/download.png";
	$perm["upload"]["alt"] = $lang->def("_ALT_UPLOAD");
	/*$perm["add"]["img"] = getPathImage()."standard/add.png";
	$perm["add"]["alt"] = $lang->def("_ADD");
	$perm["mod"]["img"] = getPathImage()."standard/edit.png";
	$perm["mod"]["alt"] = $lang->def("_MOD");
	$perm["del"]["img"] = getPathImage()."standard/delete.png";
	$perm["del"]["alt"] = $lang->def("_DEL");*/
	$perm["moderate"]["img"] = '';
	$perm["moderate"]["alt"] = $lang->def("_MODERATE");

	$ssel->setPermList($perm);

	$url = "index.php?modname=public_forum&amp;op=modforumaccess&amp;idForum=".$idForum;
	$back_url = "index.php?modname=public_forum&amp;op=forum";
	$ssel->setLinks($url, $back_url);

	$op = $ssel->getOp();

	if (($op == "main") || ($op == "manual_init") || ($op == "orgchartselector"))
		$saved_data=loadForumSavedPerm($idForum);

	$page_body="";
	$full_page="";
	
	switch($op) {

		case "main": {
			$ssel->setSavedData($saved_data);
			$page_body=$ssel->loadSimpleSelector(false, true);
		} break;

		case "manual_init":{

			// Saving permissions of simple selector
			$save_info=$ssel->getSaveInfo();
			saveForumPerm($idForum, $save_info["selected"], $save_info["database"]);

			$ssel->setSavedData($saved_data);
			$full_page = $ssel->loadManualSelector($lang->def( '_FORUM_PERM' ));
		} break;
		case "manual": {
			$full_page = $ssel->loadManualSelector($lang->def( '_FORUM_PERM' ));
		} break;

		case "save_manual": {

			// Saving permissions of manual selector
			$save_info=$ssel->getSaveInfo();
			saveForumPerm($idForum, $save_info["selected"], $save_info["database"]);

			Util::jump_to(str_replace("&amp;", "&", $url));
		} break;

		case "save": {

			// Saving permissions of simple selector
			$save_info=$ssel->getSaveInfo();
			saveForumPerm($idForum, $save_info["selected"], $save_info["database"]);

			Util::jump_to(str_replace("&amp;", "&", $back_url));
		} break;
		
		case 'orgchartselector':
			$ssel->setSavedData($saved_data);
			$page_body = $ssel->orgchartSelector();
		break;
		
		case 'save_org':
			$save_info=$ssel->getSaveInfoOrg();
			saveForumPerm($idForum, $save_info["selected"], $save_info["database"]);
			Util::jump_to(str_replace("&amp;", "&", $back_url));
		break;

	}

	if (!empty($full_page))
		$out->add($full_page);

	if (!empty($page_body)) {
		// If we have only the page body, then better to add the area title.
		$ta_array=array();
		$ta_array["index.php?modname=public_forum&amp;op=forum"] = $lang->def("_FORUM");
		$ta_array[]=$lang->def( '_FORUM_PERM' );

		$out->add(getTitleArea($ta_array, 'forum', $lang->def('_FORUM')));
		$out->add("<div class=\"std_block\">");
		$out->add($page_body);
		$out->add("</div>");
	}
}


function saveForumPerm($idForum, $selected_items, $database_items) {

		$pl=getForumPermList();
		$acl_manager=& Docebo::user()->getACLManager();
		foreach($pl as $key=>$val) {
			if ((isset($selected_items[$val])) && (is_array($selected_items[$val]))) {

				$role_id="/lms/course/public/public_forum/".$idForum."/".$val;
				$role=$acl_manager->getRole(false, $role_id);
				if (!$role)
					$idst=$acl_manager->registerRole($role_id, "");
				else
					$idst=$role[ACL_INFO_IDST];

				foreach($selected_items[$val] as $pk=>$pv) {
					if ((!isset($database_items[$val])) || (!is_array($database_items[$val])) ||
						(!in_array($pv, array_keys($database_items[$val])))) {
							$acl_manager->addToRole($idst, $pv);
					}
				}

				if ((isset($database_items[$val])) && (is_array($database_items[$val])))
					$to_rem=array_diff(array_keys($database_items[$val]), $selected_items[$val]);
				else
					$to_rem=array();
				foreach($to_rem  as $pk=>$pv) {
					$acl_manager->removeFromRole($idst, $pv);
				}

			}
		}

	//include_once($GLOBALS['where_cms']."/lib/lib.reloadperm.php");
	//setCmsReloadPerm();
}

function getForumPermList() {
	return array("view", "write", "upload", /*"add", "mod", "del",*/ "moderate");
}


function loadForumSavedPerm($idForum) {
	$res=array();
	$pl=getForumPermList();
	$acl_manager=& Docebo::user()->getACLManager();

	foreach($pl as $key=>$val) {

		$role_id="/lms/course/public/public_forum/".$idForum."/".$val;
		$role=$acl_manager->getRole(false, $role_id);

		if (!$role) {
			$res[$val]=array();
		}
		else {
			$idst=$role[ACL_INFO_IDST];
			$res[$val]=array_flip($acl_manager->getRoleMembers($idst));
		}
	}

	return $res;
}

//---------------------------------------------------------------------------//

function thread() {
	if(!checkPublicForumPerm('view', (int)$_GET['idForum']))
		die("You can't access'");

	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.navbar.php');
	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('forum');

	$mod_perm 	= checkPerm('mod', true);
	$id_forum 	= importVar('idForum', true, 0);
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	
	$ord 		= importVar('ord');
	$jump_url	= 'index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum;
	$acl_man 	=& Docebo::user()->getAclManager();
	$all_read	= importVar('allread', true, 0);
	
	if ($all_read)
		unset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID]);

	list($title, $tot_thread, $locked_f) = sql_fetch_row(sql_query("
	SELECT title, num_thread, locked
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND idForum = '$id_forum'"));

	$nav_bar 	= new NavBar('ini', Get::sett('visuItem'), $tot_thread, 'link');
	$ini 		= $nav_bar->getSelectedElement();
	$ini_page 	= $nav_bar->getSelectedPage();
	$nav_bar->setLink($jump_url.'&amp;ord='.$ord);

	$query_thread = "
	SELECT t.idThread, t.author AS thread_author, t.posted, t.title, t.num_post, t.num_view, t.locked, t.erased, t.rilevantForum
	FROM ".$GLOBALS['prefix_lms']."_forumthread AS t LEFT JOIN
			".$GLOBALS['prefix_lms']."_forummessage AS m ON ( t.last_post = m.idMessage )
	WHERE t.idForum = '$id_forum'";
	
	if(PUBLIC_FORUM_EDITION_ID) $query_thread .= " AND id_edition = '".PUBLIC_FORUM_EDITION_ID."'";
	
	$query_thread .= " ORDER BY t.rilevantForum DESC " ;
	switch($ord) {
		case "obji"		: $query_thread .= " , t.title DESC " ;	break;
		case "obj" 		: $query_thread .= " , t.title " ;		break;
		case "authi"	: $query_thread .= " , t.author DESC " ;	break;
		case "auth" 	: $query_thread .= " , t.author " ;		break;
		case "posti" 	: $query_thread .= " , m.posted " ;		break;
		case "post"		:
		default 		: {
			$ord = 'post';
			$query_thread .= " , m.posted DESC " ;	break;
		}
	}
	$query_thread .= " LIMIT $ini, ".Get::sett('visuItem');
	$re_thread = sql_query($query_thread);

	$re_last_post = sql_query("
	SELECT m.idThread, t.author AS thread_author, m.posted, m.title, m.author  AS mess_author, m.generator
	FROM ".$GLOBALS['prefix_lms']."_forumthread AS t LEFT JOIN
		".$GLOBALS['prefix_lms']."_forummessage AS m ON ( t.last_post = m.idMessage )
	WHERE t.idForum = '".$id_forum."'");
	while(list($idT_p, $id_ta, $posted, $title_p, $id_a, $is_gener) = sql_fetch_row($re_last_post)) {

		$last_authors[$id_ta] = $id_ta;
		if($posted !== NULL) {

			$last_post[$idT_p]['info'] = Format::date($posted).'<br />'.substr(strip_tags($title_p), 0, 15).' ...';
			$last_post[$idT_p]['author'] = $id_a;
			$last_authors[$id_a] = $id_a;
		}
	}
	if(isset($last_authors)) {
		$authors_names =& $acl_man->getUsers($last_authors);
	}
	$page_title = array(
		'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
		$title
	);
	$GLOBALS['page']->add(
		 getTitleArea($page_title, 'forum')
		// .'<div class="std_block">'
		// .Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search&amp;idForum='.$id_forum)
		// .'<div class="quick_search_form">'
		// .'<label for="search_arg">'.$lang->def('_SEARCH_LABEL').'</label> '
		// .Form::getInputTextfield(	'search_t',
		// 							'search_arg',
		// 							'search_arg',
		// 							'',
		// 							$lang->def('_SEARCH'), 255, '' )
		// .'<input class="search_b" type="submit" id="search_button" name="search_button" value="'.$lang->def('_SEARCH').'" />'
		// .'</div>'
		// .Form::closeForm()
	, 'content');

	// $tb = new Table(Get::sett('visuItem'), $lang->def('_THREAD_CAPTION'), $lang->def('_THRAD_SUMMARY'), 'thread-summary');

	$threads_order = '<div class="dropdown pull-right">
										  <button class="btn btn-default dropdown-toggle" type="button" id="threadsOrder" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										    <span class="glyphicon glyphicon-filter"></span> &nbsp;
										    <span class="hidden-xs">Ordina &nbsp;</span>
										    <span class="caret"></span>
										  </button>
										  <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="threadOrder">
										    <li>
													<a href="'.$jump_url.'&amp;ord=posti">
														<span class="glyphicon glyphicon-sort-by-order"></span> &nbsp;
														<span>Data (crescente)</span>
													</a>
										    </li>
										    <li>
										    	<a href="'.$jump_url.'&amp;ord=post">
										    		<span class="glyphicon glyphicon-sort-by-order-alt"></span> &nbsp;
										    		<span>Data (decrescente)</span>
										    	</a>
										    </li>
										  </ul>
										</div>';

	$tb = new Table(Get::sett('visuItem'), $lang->def('_THREAD_CAPTION').$threads_order, $lang->def('_THRAD_SUMMARY'), 'thread-summary mobile-reverse');

	// $img_up 	= '<img src="'.getPathImage().'standard/ord_asc.png" alt="'.$lang->def('_ORD_ASC').'" />';
	// $img_down 	= '<img src="'.getPathImage().'standard/ord_desc.png" alt="'.$lang->def('_ORD_DESC').'" />';

	// $cont_h = array(
	// 	'<img src="'.getPathImage().'standard/msg_read.png" title="'.$lang->def('_FREET').'" alt="'.$lang->def('_FREE').'" />',
	// 	'<a href="'.$jump_url.'&amp;ord='.( $ord == 'obj' ? 'obji' : 'obj' ).'" title="'.$lang->def('_ORDER_BY').'">'
	// 		.( $ord == 'obj' ? $img_up : ( $ord == 'obji' ? $img_down : '' ) ).$lang->def('_THREAD').'</a>',
	// 	$lang->def('_NUMREPLY'),
	// 	'<a href="'.$jump_url.'&amp;ord='.( $ord == 'auth' ? 'authi' : 'auth' ).'" title="'.$lang->def('_ORDER_BY').'">'
	// 		.( $ord == 'auth' ? $img_up : ( $ord == 'authi' ? $img_down : '' ) ).$lang->def('_AUTHOR').'</a>',
	// 	//$lang->def('_NUMVIEW'),
	// 	//$lang->def('_DATE'),
	// 	'<a href="'.$jump_url.'&amp;ord='.( $ord == 'post' ? 'posti' : 'post' ).'" title="'.$lang->def('_ORDER_BY').'">'
	// 		.( $ord == 'post' ? $img_up : ( $ord == 'posti' ? $img_down : '' ) ).$lang->def('_LASTPOST').'</a>'
	// );
	// $type_h = array('image', '', 'align_center', 'align_center', 'image',
	// //'align_center',
	// 'align_center');
	// if($mod_perm || $moderate) {

	// 	//$cont_h[] = '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" />';
	// 	$type_h[] = 'image';
	// 	//$cont_h[] = '<img src="'.getPathImage().'standard/move.png" alt="'.$lang->def('_MOVE').'" title="'.$lang->def('_MOVETHREAD_TITLE').'" />';
	// 	$type_h[] = 'image';
	// 	//$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />';
	// 	$type_h[] = 'image';
	// }
	
	$type_h = array(
		'hidden-xs text-center',
		'col-xs-12 col-sm-3 text-left',
		'col-xs-12 col-sm-2 col-lg-1 text-left',
		'col-xs-12 col-sm-2 text-left',
		'col-xs-12 col-sm-4 text-left',
		'col-xs-12 col-sm-1 col-lg-2 text-center clearfix'
	);

	$tb->setColsStyle($type_h);
	//$tb->addHead($cont_h);
	while(list($idT, $t_author, $posted, $title, $num_post, $num_view, $locked, $erased, $important) = sql_fetch_row($re_thread)) {
		
		$msg_for_page = Get::sett('visuItem');
		if (isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT]) && $_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT] != 'new_thread')
		{
			$unread_message = $_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT];
			$first_unread_message = $num_post - $unread_message + 2;
			if ($first_unread_message % $msg_for_page)
				$ini_unread = ($first_unread_message - ($first_unread_message % $msg_for_page)) / $msg_for_page + 1;
			else
				$ini_unread = $first_unread_message / $msg_for_page;
			$first_unread_message_in_page = $first_unread_message % $msg_for_page;
		}
		else
		{
			$first_unread_message_in_page = 1;
			$ini_unread = 1;
		}
		
		if ((($num_post + 1) % $msg_for_page))
			$number_of_pages = (($num_post + 1) - (($num_post + 1) % $msg_for_page)) / $msg_for_page + 1;
		else
			$number_of_pages = ($num_post + 1) / $msg_for_page;
		
		$c_css = '';
		// thread author
		$t_author = ( isset($authors_names[$t_author])
				? ( $authors_names[$t_author][ACL_INFO_LASTNAME].$authors_names[$t_author][ACL_INFO_FIRSTNAME] == '' ?
					$acl_man->relativeId($authors_names[$t_author][ACL_INFO_USERID]) :
					$authors_names[$t_author][ACL_INFO_LASTNAME].' '.$authors_names[$t_author][ACL_INFO_FIRSTNAME] )
				: $lang->def('_UNKNOWN_AUTHOR') );

		// last post author
		if(isset($last_post[$idT])) {

			$author = $last_post[$idT]['author'];
			$last_mess_write = $last_post[$idT]['info'].' ( '.$lang->def('_BY').': <span class="mess_author">'
				.( isset($authors_names[$author])
					? ( $authors_names[$author][ACL_INFO_LASTNAME].$authors_names[$author][ACL_INFO_FIRSTNAME] == '' ?
						$acl_man->relativeId($authors_names[$author][ACL_INFO_USERID]) :
						$authors_names[$author][ACL_INFO_LASTNAME].' '.$authors_names[$author][ACL_INFO_FIRSTNAME] )
					: $lang->def('_UNKNOWN_AUTHOR') )
				.'</span> )';
		} else {
			$last_mess_write = $lang->def('_NONE');
		}
		// status of the thread
		
		if($erased) {
			$status = '<span class="glyphicon glyphicon-trash" title="'.$lang->def('_FREE').'"></span>';
		} elseif($locked) {
			$status = '<span class="glyphicon glyphicon-lock" title="'.$lang->def('_LOCKED').'"></span>';
		} elseif(isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT])) {
			$status = '<span class="glyphicon glyphicon-folder-close" title="'.$lang->def('_UNREAD').'"></span>';
			$c_css = ' class="text_bold"';
		} else {
			$status = '<span class="glyphicon glyphicon-folder-open" title="'.$lang->def('_FREE').'"></span>';
		}
		$content = array($status);
		//'<img src="'.getPathImage().'standard/important.png" alt="'.$lang->def('_IMPORTANT').'" />'
		$content_temp = ( $erased && !$mod_perm ?
						'<div class="forumErased">'.$lang->def('_OPERATION_SUCCESSFUL').'</div>' :
						//($important ? '<img src="'.getPathImage().'standard/important.png" alt="'.$lang->def('_IMPORTANT').'" />' : '').' <a'.$c_css.' href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'">'.$title.'</a>');
						// ($important ? '<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;' : '').' <a'.$c_css.' href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'">'.$title.'</a>');
						($important ? '<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;' : '').' <a'.$c_css.' href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'"><strong>'.$title.'</strong></a>');
		
		$content_temp .= '<p class="forum_pages">';
		if ($first_unread_message_in_page != 1) {
			$content_temp .= '<a'.$c_css.' href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'&amp;firstunread='.$first_unread_message_in_page.'&amp;ini='.$ini_unread.($first_unread_message_in_page != 1 ? '&#firstunread' : '').'">'.$lang->def('_FIRST_UNREAD').'</a> ';
		}
		if ($number_of_pages > 1)
		{	
			if ($number_of_pages > 4)
			{
				$content_temp .= '( <a href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'&amp;ini=1">1</a> ... ';
				$content_temp .= ' <a href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'&amp;ini='.($number_of_pages - 2).'">'.($number_of_pages - 2).'</a> ';
				$content_temp .= ' <a href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'&amp;ini='.($number_of_pages - 1).'">'.($number_of_pages - 1).'</a> ';
				$content_temp .= ' <a href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'&amp;ini='.$number_of_pages.'">'.$number_of_pages.'</a> )'; 
			}
			else
			{
				$content_temp .= '(';
				for ($i = 1; $i <= $number_of_pages; $i++)
					$content_temp .= ' <a href="index.php?modname=public_forum&amp;op=message&amp;idThread='.$idT.'&amp;ini='.$i.'">'.$i.'</a> ';
				$content_temp .= ')';
			}
		}
		$content_temp .= '</p>';
		$content[] = $content_temp;
		
		$content[] = '<p>Risposte: <span class="badge">'.$num_post.'</span></p>'
									.'<p>Visite: <span class="badge">'.$num_view.'</span></p>'
									.( isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT]) && $_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT] != 'new_thread'
										? '<br />(<span class="forum_notread">'.$_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT].' '.$lang->def('_ADD').')</span>'
										: ( isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT]) && $_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT] == 'new_thread'
											? '<br />(<span class="forum_notread">'.$lang->def('_NEW_THREAD').')</span>'
											: '') );

		$content[] = $t_author;
		//$content[] = $num_view;
		//$content[] = Format::date($posted);

		$mobile_content = $content;

		$content[] = $last_mess_write;

		if($mod_perm || $moderate) {

			$thread_functions = '<div class="btn-group thread_functions visible-xs visible-lg">
														<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=modthread&amp;idThread='.$idT.'" title="'.$lang->def('_MOD').' : '.strip_tags($title).'">
															<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
														</a>
														<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=movethread&amp;id_forum='.$id_forum.'&amp;id_thread='.$idT.'">
															<span class="glyphicon glyphicon-transfer" aria-hidden="true"></span>
														</a>
														<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=delthread&amp;idThread='.$idT.'" title="'.$lang->def('_DEL').' : '.strip_tags($title).'">
															<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
														</a>
													</div>
													<div class="dropdown hidden-xs hidden-lg">
														<button class="btn btn-default dropdown-toggle" type="button" id="threadsFunctions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
														  <span class="glyphicon glyphicon-cog"></span> &nbsp;<span class="caret"></span>
														</button>
														<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="threadFunctions">
															<li>
															  <a href="index.php?modname=public_forum&amp;op=modthread&amp;idThread='.$idT.'">
															  	<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Modifica
															  </a>
															</li>
															<li>
															  <a href="index.php?modname=public_forum&amp;op=movethread&amp;id_forum='.$id_forum.'&amp;id_thread='.$idT.'">
															  	<span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Sposta
															  </a>
															</li>
															<li>
															  <a href="index.php?modname=public_forum&amp;op=delthread&amp;idThread='.$idT.'">
															  	<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Elimina
															  </a>
															</li>
														</ul>
													</div>';

			$content[] = $thread_functions;

			// $content[] = '<a href="index.php?modname=public_forum&amp;op=modthread&amp;idThread='.$idT.'" '
			// 	.'title="'.$lang->def('_MOD').' : '.strip_tags($title).'">'
			// 	.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.strip_tags($title).'" /></a>';
			// $content[] = '<a href="index.php?modname=public_forum&amp;op=movethread&amp;id_forum='.$id_forum.'&amp;id_thread='.$idT.'"><img src="'.getPathImage().'standard/move.png" alt="'.$lang->def('_MOVE').'" title="'.$lang->def('_MOVETHREAD_TITLE').'" /></a>';
			// $content[] = '<a href="index.php?modname=public_forum&amp;op=delthread&amp;idThread='.$idT.'" '
			// 	.'title="'.$lang->def('_DEL').' : '.strip_tags($title).'">'
			// 	.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.strip_tags($title).'" /></a>';
		}
		$tb->addBody($content);
	}

	// NOTE: If notify request register it
	require_once(_adm_.'/lib/lib.usernotifier.php');

	$can_notify = usernotifier_getUserEventStatus(getLogUserId(), 'ForumNewThread');

	if(isset($_GET['notify']) && $can_notify) {
		if(issetNotify('forum', $id_forum, getLogUserId())) {
			$re = unsetNotify('forum', $id_forum, getLogUserId());
			$is_notify = !$re;
		} else {
			$re = setNotify('forum', $id_forum, getLogUserId());
			$is_notify = $re;
		}
		if($re) $GLOBALS['page']->add(getResultUi($lang->def('_NOTIFY_CHANGE_STATUS_CORRECT')), 'content');
		else $GLOBALS['page']->add(getErrorUi($lang->def('_NOTIFY_CHANGE_STATUS_FAILED')), 'content');
	} elseif($can_notify) {
		$is_notify = issetNotify('forum', $id_forum, getLogUserId());
	}

	$text_inner = '<div class="btn-group col-lg-9 visible-lg">';
	if(!$locked_f  && checkPublicForumPerm('view', $id_forum)/*checkPerm('write', true)*/) {
		$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=addthread&amp;idForum='.$id_forum.'">
											<span class="glyphicon glyphicon-plus-sign"></span>&nbsp;
											<span>'.$lang->def('_ADDTHREAD').'</span>
										</a>';
	}
	if($can_notify) {
		$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=thread&amp;notify=1&amp;idForum='.$id_forum.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-star"></span>&nbsp;
											<span>'.( !$is_notify ? $lang->def('_NOTIFY_ME_FORUM') : $lang->def('_UNNOTIFY_ME_FORUM') ).'</span>
										</a>';
	}
	if($mod_perm) {
		$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=modstatus&amp;idForum='.$id_forum.'">
											<span class="glyphicon glyphicon-lock"></span>&nbsp;
											<span>'.( $locked_f ? $lang->def('_UNLOCKFORUM') : $lang->def('_LOCKFORUM') ).'</span>
										</a>';
	}
	$text_inner .= '</div>';
	$text_inner .= '<div class="dropdown col-xs-5 col-sm-4 hidden-lg pull-left">
										<button class="btn btn-default dropdown-toggle" type="button" id="tableActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										  <span class="glyphicon glyphicon-cog"></span> &nbsp;
										  Operazioni &nbsp;
										  <span class="caret"></span>
										</button>
										<ul class="dropdown-menu dropdown-menu-left" aria-labelledby="tableActions">';

	if(!$locked_f  && checkPublicForumPerm('view', $id_forum)/*checkPerm('write', true)*/) {
		$text_inner .= '<li><a href="index.php?modname=public_forum&amp;op=addthread&amp;idForum='.$id_forum.'">
											<span class="glyphicon glyphicon-plus-sign"></span>&nbsp;
											<span>'.$lang->def('_ADDTHREAD').'</span>
										</a></li>';
	}
	if($can_notify) {
		$text_inner .= '<li><a href="index.php?modname=public_forum&amp;op=thread&amp;notify=1&amp;idForum='.$id_forum.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-star"></span>&nbsp;
											<span>'.( !$is_notify ? $lang->def('_NOTIFY_ME_FORUM') : $lang->def('_UNNOTIFY_ME_FORUM') ).'</span>
										</a></li>';
	}
	if($mod_perm) {
		$text_inner .= '<li><a href="index.php?modname=public_forum&amp;op=modstatus&amp;idForum='.$id_forum.'">
											<span class="glyphicon glyphicon-lock"></span>&nbsp;
											<span>'.( $locked_f ? $lang->def('_UNLOCKFORUM') : $lang->def('_LOCKFORUM') ).'</span>
										</a></li>';
	}

	$text_inner .= '</ul>
								</div>
								<div class="col-xs-7 col-sm-6 col-md-4 col-lg-3 pull-right">'
								.Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search')
								.'<div class="input-group">'
								.Form::getSearchInputTextfield('', '', '', 'Cerca argomento...', '', '', '', '')
								.'<div class="input-group-btn"><button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-search"></span></button></div>'
								.'</div>'
								.Form::closeForm()
								.'</div>';
	
	if($text_inner != '') $tb->addActionAdd($text_inner);
	if($mod_perm || $moderate) {
					
		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=delthread]');
	}
	
	$GLOBALS['page']->add($nav_bar->getNavBar($ini), 'content');
	//if($text_inner != '') $GLOBALS['page']->add('<div class="forum_action_top"><ul class="link_list_inline">'.$text_inner.'</ul></div>', 'content');
	if (isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID]) && count($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID]))
		$GLOBALS['page']->add('<div><p align="right"><a href="index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;allread=1">'.$lang->def('_ALL_THREAD_READ').'</a></p>', 'content');
		$GLOBALS['page']->add($tb->getTable(), 'content');
		//if($text_inner != '') $GLOBALS['page']->add('<div class="forum_action_bottom"><ul class="link_list_inline">'.$text_inner.'</ul></div>', 'content');
		$GLOBALS['page']->add(
			$nav_bar->getNavBar($ini)
			.'</div>', 'content');
}

//---------------------------------------------------------------------------//

function addthread() {
	checkPublicForumPerm('view', (int)$_GET['idForum']);

	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum');
	$id_forum = importVar('idForum', true, 0);

	list($title) = sql_fetch_row(sql_query("
	SELECT title
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND idForum = '".$id_forum."'"));

	$page_title = array(
		'index.php?modname=public_forum&amp;forum' => $lang->def('_FORUM'),
		'index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum => $title,
		$lang->def('_NEW_THREAD')
	);
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'forum', $lang->def('_FORUM'))
		.'<div class="std_block">'
		.getBackUi('index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum, $lang->def('_BACK'))
		.Form::openForm('form_forum', 'index.php?modname=public_forum&amp;op=insthread', false, false, 'multipart/form-data')
		.Form::openElementSpace()

		.Form::getHidden('idForum', 'idForum', $id_forum)
		.Form::getTextfield($lang->def('_SUBJECT'), 'title', 'title', 255)
		.Form::getTextarea($lang->def('_TEXTOF'), 'textof', 'textof')
	, 'content');
	if(checkPublicForumPerm('upload', (int)$_GET['idForum'])) {

		$GLOBALS['page']->add(Form::getFilefield($lang->def('_UPLOAD'), 'attach', 'attach'), 'content');
	}
	$is_important = 1;//array($lang->def('_NO'), $lang->def('_YES'));
	if (checkPerm('mod', true) || checkPublicForumPerm('moderate', (int)$_GET['idForum']))
		$GLOBALS['page']->add(Form::getCheckbox($lang->def('_IMPORTANT_THREAD'), 'important', 'important', $is_important), 'content');
	$GLOBALS['page']->add(
		Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('post_thread', 'post_thread', $lang->def('_SEND'))
		.Form::getButton('undp', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>'
	, 'content');
}

//---------------------------------------------------------------------------//

function save_file($file) {
	require_once(_base_.'/lib/lib.upload.php');

	$path = '/appLms/'.Get::sett('pathforum');

	if($file['name'] != '') {

		$savefile = PUBLIC_FORUM_COURSE_ID.'_'.rand(0,100).'_'.time().'_'.$file['name'];
		if(!file_exists($GLOBALS['where_files_relative'].$path.$savefile)) {

			sl_open_fileoperations();
			if(!sl_upload($file['tmp_name'], $path.$savefile)) {

				$savefile = '';
			}
			sl_close_fileoperations();
			return $savefile;
		}
	}
	return '';
}

function delete_file( $name ) {
	require_once(_base_.'/lib/lib.upload.php');

	$path = '/appLms/'.Get::sett('pathforum');
	if($name != '') return sl_unlink($path.$name);
}

function insthread() {
	checkPublicForumPerm('write', (int)$_GET['idForum']);

	$lang =& DoceboLanguage::createInstance('forum');
	$id_forum = importVar('idForum', true , 0);
	if(isset($_POST['undo'])) Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum);

	list($forum_title) = sql_fetch_row(sql_query("
	SELECT title
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND idForum = '".$id_forum."'"));

	$locked = false;
	if(!checkPublicForumPerm('moderate', (int)$_GET['idForum'])) {

		$query_view_forum = "
		SELECT idMember, locked
		FROM ".$GLOBALS['prefix_lms']."_forum AS f
		WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND f.idForum = '".$id_forum."'";
		$re_forum = sql_query($query_view_forum);
		while(list($id_m, $lock_s) = sql_fetch_row($re_forum)) {

			$locked = $lock_s;
			if($id_m != NULL) $members[] = $id_m;
		}
	}
	$continue = false;
	if(!isset($members)) $continue = true;
	else {
		$acl 	=& Docebo::user()->getAcl();
		$all_user_idst = $acl->getSTGroupsST(getLogUserId());
		$all_user_idst[] = getLogUserId();

		$can_access = array();
		$can_access = array_intersect($members, $all_user_idst);
		if(!empty($can_access)) $continue = true;
	}
	if(!$continue) Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=err_cannotsee');
	if($locked) Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=err_lock');

	if($_POST['title'] == '') {
		if($_POST['textof'] != '') {

			$_POST['title'] = substr(strip_tags($_POST['textof']), 0, 50).( count($_POST['textof']) > 50 ? '...' : '' );
		} else {

			$_POST['title'] = $lang->def('_NOTITLE');
		}
	}
	$now = date("Y-m-d H:i:s");
	$important = importVar('important', true, '0');
	$ins_query = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_forumthread
	( idForum, id_edition, title, author, num_post, last_post, posted, rilevantForum )
	VALUES (
		'".$id_forum."',
		'".PUBLIC_FORUM_EDITION_ID."',
		'".$_POST['title']."',
		'".getLogUserId()."',
		 0,
		 0,
		 '".$now ."',
		 '".$important."')";
	if(!sql_query($ins_query)) Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=err_ins');
	list($id_thread) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

	$name_file = '';
	if(($_FILES['attach']['name'] != '') && checkPublicForumPerm('upload', (int)$_GET['idForum'])) {

		$name_file = save_file($_FILES['attach']);
	}

	$ins_mess_query = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_forummessage
	( idThread, idCourse, title, textof, author, posted, answer_tree, attach, generator )
	VALUES (
		'".$id_thread."',
		'".(int)PUBLIC_FORUM_COURSE_ID."',
		'".$_POST['title']."',
		'".$_POST['textof']."',
		'".getLogUserId()."',
		'".$now ."',
		'/".$now ."',
		'".addslashes($name_file)."',
		'1' ) ";
	if(!sql_query( $ins_mess_query )) {

		sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_forumthread
		WHERE idThread = '$id_thread'");
		delete_file($name_file);

		Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=err_ins2');
	}
	list($id_message) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forumthread
	SET last_post = '$id_message'
	WHERE idThread = '$id_thread'");

	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forum
	SET num_thread = num_thread + 1,
		num_post = num_post + 1,
		last_post = '$id_message'
	WHERE idForum = '$id_forum'");

	$course_name = PUBLIC_FORUM_COURSE_NAME;

	// launch notify
	require_once(_base_.'/lib/lib.eventmanager.php');

	$msg_composer = new EventMessageComposer();

	$msg_composer->setSubjectLangText('email', '_SUBJECT_NOTIFY_THREAD', false);
	$msg_composer->setBodyLangText('email', '_NEW_THREAD_INSERT_IN_FORUM', array(	'[url]' => Get::sett('url'),
																		'[course]' => PUBLIC_FORUM_COURSE_NAME,
																		'[forum_title]' => $forum_title,
																		'[thread_title]' => $_POST['title'] ) );

	$msg_composer->setBodyLangText('sms', '_NEW_THREAD_INSERT_IN_FORUM_SMS', array(	'[url]' => Get::sett('url'),
																		'[course]' => PUBLIC_FORUM_COURSE_NAME,
																		'[forum_title]' => $forum_title,
																		'[thread_title]' => $_POST['title'] ) );

	launchNotify('forum', $id_forum, $lang->def('_NEW_THREAD'), $msg_composer);

	Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread);
}

//---------------------------------------------------------------------------//

function modthread() {
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum', 'lms');
	$id_thread 	= importVar('idThread', true, 0);
	$ini 	= importVar('ini');

	$mod_perm	= checkPerm('mod', true);
	$acl_man 	=& Docebo::user()->getAclManager();

	// retrive info about message
	$mess_query = "
	SELECT idMessage, title, textof, author
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE idThread = '".$id_thread."' AND generator = '1'";
	list($id_message, $title, $textof, $author) = sql_fetch_row(sql_query($mess_query));

	// Some info about forum and thread
	$thread_query = "
	SELECT idForum
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'";
	list($id_forum) = sql_fetch_row(sql_query($thread_query));
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	
	if(!$moderate && !$mod_perm && ($author != getLogUserId()) ) die("You can't access");
	
	$forum_query = "
	SELECT title, locked
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".$id_forum."'";
	list($forum_title, $locked_f) = sql_fetch_row(sql_query($forum_query));

	$page_title = array(
		'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
		'index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum => $forum_title,
		$lang->def('_MOD')
	);

	$GLOBALS['page']->add(
		getTitleArea($page_title, 'forum')
		.'<div class="std_block">'
		.( isset($_GET['search'])
			? getBackUi('index.php?modname=public_forum&op=search&amp;ini='.$ini, $lang->def('_BACK'))
			: getBackUi('index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum, $lang->def('_BACK'))
		)
		.Form::openForm('form_forum', 'index.php?modname=public_forum&amp;op=upthread', false, false, 'multipart/form-data')
		.Form::openElementSpace()

		.Form::getHidden('search', 'search', (isset($_GET['search']) ? '1' : '0' ) )
		.Form::getHidden('ini', 'ini', importVar('ini') )
		.Form::getHidden('idThread', 'idThread', $id_thread)
		.Form::getTextfield($lang->def('_SUBJECT'), 'title', 'title', 255, $title)
		.Form::getTextarea($lang->def('_TEXTOF'), 'textof', 'textof', $textof)
	, 'content');
	if(checkPublicForumPerm('upload', $id_forum)) {

		$GLOBALS['page']->add(Form::getFilefield($lang->def('_UPLOAD'), 'attach', 'attach'), 'content');
	}
	$is_important = 1;//array($lang->def('_NO'), $lang->def('_YES'));
	if (checkPublicForumPerm('moderate', $id_forum) || checkPerm('mod', true))
		$GLOBALS['page']->add(Form::getCheckbox($lang->def('_IMPORTANT_THREAD'), 'important', 'important', $is_important), 'content');
	$GLOBALS['page']->add(
		Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('post_thread', 'post_thread', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function upthread() {
	$id_thread 	= importVar('idThread', true, 0);
	$ini 	= importVar('ini');
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	$mod_perm	= checkPerm('mod', true);

	$lang =& DoceboLanguage::createInstance('forum');

	// retrive info about message
	$mess_query = "
	SELECT idMessage, author, attach
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE  idThread = '".$id_thread."' AND generator = '1'";
	list($id_message, $author, $attach) = sql_fetch_row(sql_query($mess_query));
	if(isset($_POST['undo'])) {

		if($_POST['search'] == 1) Util::jump_to('index.php?modname=public_forum&op=search&amp;ini='.$ini);
		else Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini);
	}

	if(!$moderate && !$mod_perm && ($author != getLogUserId()) ) die("You can't access");

	list($id_forum, $locked_t, $erased_t) = sql_fetch_row(sql_query("
	SELECT idForum, locked, erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'"));

	if($locked_t ||$erased_t && (!$mod_perm && !$moderate)) {
		if($_POST['search'] == 1) Util::jump_to('index.php?modname=public_forum&op=search&amp;ini='.$ini);
		else Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini.'&amp;result=err_lock');
	}
	if($_POST['title'] == '') $_POST['title'] = substr(strip_tags($_POST['textof']), 0, 50).'...';

	$now = date("Y-m-d H:i:s");

	//save attachment
	$name_file = $attach;
	if($_FILES['attach']['name'] != '' && checkPublicForumPerm('upload', $id_forum) ) {

		delete_file($attach);
		$name_file = save_file($_FILES['attach']);
	}
	$upd_mess_query = "
	UPDATE ".$GLOBALS['prefix_lms']."_forummessage
	SET title = '".$_POST['title']."',
		textof = '".$_POST['textof']."',
		attach = '".$name_file."',
		modified_by = '".getLogUserId()."',
		modified_by_on = '".$now."'
	WHERE idMessage = '".$id_message."' AND idCourse = '".PUBLIC_FORUM_COURSE_ID."'";
	if(!sql_query($upd_mess_query)) {

		delete_file($name_file);
		if($_POST['search'] == 1) Util::jump_to('index.php?modname=public_forum&op=search&amp;ini='.$ini);
		else Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=err_ins');
	}
	$is_rilevant = importVar('important', true, 0);
	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forumthread
	SET title = '".$_POST['title']."'," .
		" rilevantForum = '".$is_rilevant."'
	WHERE idThread = '".$id_thread."'");
	if($_POST['search'] == 1) Util::jump_to('index.php?modname=public_forum&op=search&amp;ini='.$ini);
	else Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=ok');
}

//---------------------------------------------------------------------------//

function delthread() {

	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum', 'lms');
	$id_thread = importVar('idThread', true, 0);
	$ini = importVar('ini');

	$thread_query = "
	SELECT idForum, title, last_post
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'";
	list($id_forum, $thread_title, $last_post) = sql_fetch_row(sql_query($thread_query));

	if(isset($_POST['undo'])) {
		if(Get::req('search', DOTY_INT) == 1) Util::jump_to('index.php?modname=public_forum&op=search&amp;ini='.$ini);
		else Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum);
	}
	$confirm = isset($_POST['confirm']);
	if(!$confirm) $confirm = Get::req('confirm', DOTY_INT, 0);
	if($confirm) {

		$forum_query = "
		SELECT last_post
		FROM ".$GLOBALS['prefix_lms']."_forum
		WHERE idForum = '".$id_forum."'";
		list($last_post_forum) = sql_fetch_row(sql_query($forum_query));

		$mess_query = "
		SELECT attach
		FROM ".$GLOBALS['prefix_lms']."_forummessage
		WHERE idThread = '".$id_thread."'";
		$re_mess = sql_query($mess_query);
		while(list($file) = sql_fetch_row($re_mess)) {

			if($file != '') delete_file($file);
		}
		$post_deleted = mysql_num_rows($re_mess);
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_forummessage
		WHERE idThread = '".$id_thread."'"))
			if(Get::req('search', DOTY_INT) == 1) Util::jump_to('index.php?modname=public_forum&op=search&amp;ini='.$ini);
			else Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=err_del');


		if($last_post_forum == $last_post) {

			$query_text = "
			SELECT idThread, posted
			FROM ".$GLOBALS['prefix_lms']."_forumthread
			WHERE idForum = '".$id_forum."'
			ORDER BY posted DESC";
			$re = sql_query($query_text);
			list($id_new, $post) = sql_fetch_row($re);
		}

		if(!sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forum
		SET num_thread = num_thread - 1,
			num_post = num_post - ".$post_deleted
		.( $last_post_forum == $last_post ? " , last_post = '".$id_new."' " : " " )
		." WHERE idForum = '".$id_forum."'"))
			if($_POST['search'] == 1) Util::jump_to('index.php?modname=public_forum&op=search&amp;ini='.$ini);
			else Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=err_del');

		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_forumthread
		WHERE idThread = '".$id_thread."'"))
			if($_POST['search'] == 1) Util::jump_to('index.php?modname=public_forum&op=search&amp;ini='.$ini);
			else Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=err_del');

		unsetNotify('thread', $id_thread);
		if($_POST['search'] == 1) Util::jump_to('index.php?modname=public_forum&op=search');
		else Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=ok');
	} else {

		$forum_query = "
		SELECT title
		FROM ".$GLOBALS['prefix_lms']."_forum
		WHERE idForum = '".$id_forum."'";
		list($forum_title) = sql_fetch_row(sql_query($forum_query));

		$page_title = array(
			'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
			'index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum => $forum_title,
			$lang->def('_DEL')
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'forum')
			.'<div class="std_block">'
			.Form::openForm('del_thread', 'index.php?modname=public_forum&amp;op=delthread')
			.Form::getHidden('idThread', 'idThread', $id_thread)
			.Form::getHidden('search', 'search', (isset($_GET['search']) ? '1' : '0' ) )
			.Form::getHidden('ini', 'ini', importVar('ini') )
			.getDeleteUi(
				$lang->def('_AREYOUSURE'),
				'<span>'.$lang->def('_TITLE').' :</span> '.$thread_title,
				false,
				'confirm',
				'undo'
			)
			.Form::closeForm()
			.'</div>', 'content');
	}
}

//---------------------------------------------------------------------------//

// XXX: distance
function loadDistance( $date ) {

	// yyyy-mm-dd hh:mm:ss
	// 0123456789012345678
	$year 	= substr($date, 0, 4);
	$month 	= substr($date, 5, 2);
	$day	= substr($date, 8, 2);

	$hour 	= substr($date, 11, 2);
	$minute = substr($date, 14, 2);
	$second	= substr($date,17 , 2);

	$distance = time() - mktime($hour, $minute, $second, $month, $day, $year);
	//second -> minutes
	$distance = (int)($distance / 60);
	//< 1 hour print minutes
	if( ($distance >= 0 ) && ($distance < 60) ) return $distance.' '. Lang::t('_MINUTES');

	//minutes -> hour
	$distance = (int)($distance / 60);
	if( ($distance >= 0 ) && ($distance < 60) ) return $distance.' '. Lang::t('_HOURS');

	//hour -> day
	$distance = (int)($distance / 24);
	if( ($distance >= 0 ) && ($distance < 30 ) ) return $distance.' '. Lang::t('_DAYS');

	//echo > 1 month
	return Lang::t('_ONEMONTH');
}

function message() {
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.user_profile.php');

	$lang =& DoceboLanguage::createInstance('forum', 'lms');
	$id_thread = importVar('idThread', true, 0);
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));

	if (!checkPublicForumPerm('view', $id_forum))
		die('You can\'t access!');
	
	$sema_perm 	= checkPerm('sema', true);
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	$mod_perm	= checkPerm('mod', true);
	$write_perm = checkPublicForumPerm('view', $id_forum);
	$acl_man 	=& Docebo::user()->getAclManager();

	$profile_man = new UserProfile(0);
	$profile_man->init('profile', 'framework', 'index.php?modname=public_forum&op=forum');

	// $tb 	= new Table(Get::sett('visuItem'), $lang->def('_CAPTION_FORUM_MESSAGE'), $lang->def('_CAPTION_FORUM_MESSAGE'), 'forum-message');
	$tb 	= new Table(Get::sett('visuItem'), $lang->def('_CAPTION_FORUM_MESSAGE'), $lang->def('_CAPTION_FORUM_MESSAGE'), 'forum-message');
	$tb->initNavBar('ini', 'link');
	$tb->setLink('index.php?modname=public_forum&amp;op=message&amp;idThread='.$id_thread);
	$ini 	= $tb->getSelectedElement();
	$ini_page = $tb->getSelectedPage();
	$first_unread_message = importVar('firstunread', true, 0);
	$ini_first_unread_message = importVar('ini', true, 0);
	
	$set_important = importVar('important', true, 0);
	if ($set_important == 1)
	{
		$query_set_important = "UPDATE ".$GLOBALS['prefix_lms']."_forumthread" .
								" SET rilevantForum = 1" .
								" WHERE idThread = '".$id_thread."'";
		
		$result_set_important = sql_query($query_set_important);
	}
	if ($set_important == 2)
	{
		$query_set_important = "UPDATE ".$GLOBALS['prefix_lms']."_forumthread" .
								" SET rilevantForum = 0" .
								" WHERE idThread = '".$id_thread."'";
		
		$result_set_important = sql_query($query_set_important);
	}
	
	$result = sql_fetch_row(sql_query("SELECT rilevantForum FROM ".$GLOBALS['prefix_lms']."_forumthread WHERE idThread = '".$id_thread."'"));
	$is_important = $result[0];
	
	// Some info about forum and thread
	$thread_query = "
	SELECT idForum, title, num_post, locked, erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'";
	list($id_forum, $thread_title, $tot_message, $locked_t, $erased_t) = sql_fetch_row(sql_query($thread_query));
	
	$forum_query = "
	SELECT title, locked
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".$id_forum."'";
	list($forum_title, $locked_f) = sql_fetch_row(sql_query($forum_query));
	++$tot_message;

	//set as readed if needed
	if(isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$id_thread])) unset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$id_thread]);

	if( ($ini == 0) && (!isset($_GET['result'])) ) {
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forumthread
		SET num_view = num_view + 1
		WHERE idThread = '".$id_thread."'");
	}
	$page_title = array(
		'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
		'index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum => $forum_title,
		$thread_title
	);
	if($erased_t && !$mod_perm && !$moderate) {

		$GLOBALS['page']->add(
			getTitleArea($page_title, 'forum')
			.'<div class="std_block">'
			.$lang->def('_CANNOTENTER')
			.'</div>', 'content');
		return;
	}
	// Who have semantic evaluation
	$re_sema = sql_query("
	SELECT DISTINCT idmsg
	FROM ".$GLOBALS['prefix_lms']."_forum_sema");
	while(list($msg_sema) = sql_fetch_row($re_sema)) $forum_sema[$msg_sema] = 1;

	// Find post
	$messages 		= array();
	$authors 		= array();
	$authors_names	= array();
	$authors_info	= array();
	$re_message = sql_query("
	SELECT idMessage, posted, title, textof, attach, locked, author, modified_by, modified_by_on
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE idThread = '".$id_thread."'
	ORDER BY posted
	LIMIT $ini, ".Get::sett('visuItem'));
	while($record = mysql_fetch_assoc($re_message)) {

		$messages[$record['idMessage']] 	= $record;
		$authors[$record['author']] 		= $record['author'];
		if($record['modified_by'] != 0) {
			$authors[$record['modified_by']] 	= $record['modified_by'];
		}
	}
	$authors_names =& $acl_man->getUsers($authors);
	$level_name = CourseLevel::getLevels();

	// Retriving level and number of post of the authors
	if(!empty($authors)) {

		$re_num_post = sql_query("
		SELECT u.idUser, u.level, COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_forummessage AS m, ".$GLOBALS['prefix_lms']."_courseuser AS u
		WHERE m.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND m.author = u.idUser AND m.author IN ( ".implode($authors, ',')." )
		GROUP BY u.idUser, u.level");
		while( list($id_u, $level_u, $num_post_a) = sql_fetch_row($re_num_post) ) {

			$authors_info[$id_u] = array( 'num_post' => $num_post_a, 'level' => $level_name[$level_u] );
		}
		$profile_man->setCahceForUsers($authors);
	}
	$type_h = array('forum_sender', 'forum_text');
	$cont_h = array($lang->def('_AUTHOR'), $lang->def('_TEXTOF'));
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	// Compose messagges display
	$path = $GLOBALS['where_files_relative'].'/appCore/'.Get::sett('pathphoto');
	$counter = 0;
	while(list($id_message, $message_info) = each($messages)) {
		$counter++;
		// sender info
		$m_author = $message_info['author'];

		//if(isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_AVATAR] != '') $img_size = @getimagesize($path.$authors_names[$m_author][ACL_INFO_AVATAR]);

		$profile_man->setIdUser($m_author);
		
		$author = $profile_man->getUserPanelData(false, 'normal');
		// $sender = '';
		
		// $sender = '<div class="forum_author">';
		
		// $sender = $author['actions']
		// 	.$author['display_name']
			
		// 	.( isset($authors_info[$m_author])
		// 		? '<div class="forum_level">'.$authors_info[$m_author]['level'].'</div>' : '' )
		// 	.'<br/>'
		// 	//.(strstr($author['avatar'], './templates/standard/images/standard/user.png') ? $author['photo'] : $author['avatar'])
		// 	.'<div class="forum_numpost">'.$lang->def('_NUMPOST').' : '
		// 	.( isset($authors_info[$m_author]['num_post'])
		// 		? $authors_info[$m_author]['num_post']
		// 		: 0 )
		// 	.'</div>'
			
		// 	// .'<a class="ico-wt-sprite subs_user" href="index.php?modname=public_forum&amp;op=viewprofile&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'&amp;idThread='.$id_thread.'">'
		// 	// .'<span>'.$lang->def('_VIEW_PROFILE').'</span></a>';

		// 	.'<br /><span class="glyphicon glyphicon-user"></span> &nbsp;'
		// 	.'<a href="index.php?modname=public_forum&amp;op=viewprofile&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'&amp;idThread='.$id_thread.'">'
		// 	.'<span>'.$lang->def('_VIEW_PROFILE').'</span></a>';
			
		// 	/*.( isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_AVATAR] != ''
		// 		? '<img class="forum_avatar'.( $img_size[0] > 150 || $img_size[1] > 150 ? ' image_limit' : '' ).'" src="'.$path.$authors_names[$m_author][ACL_INFO_AVATAR].'" alt="'.$lang->def('_AVATAR').'" />'
		// 		: '' )*/
			
			
		// 	/*
		// 	.( isset($authors_names[$m_author])
		// 		?( $authors_names[$m_author][ACL_INFO_LASTNAME].$authors_names[$m_author][ACL_INFO_FIRSTNAME] == '' ?
		// 			$acl_man->relativeId($authors_names[$m_author][ACL_INFO_USERID]) :
		// 			$authors_names[$m_author][ACL_INFO_LASTNAME].' '.$authors_names[$m_author][ACL_INFO_FIRSTNAME] )
		// 		: $lang->def('_UNKNOWN_AUTHOR') )
		// 	*/
		// 	/*.'<img src="'.getPathImage().'standard/identity.png" alt="&gt;" />&nbsp;'
		// 	.'<a href="index.php?modname=public_forum&amp;op=viewprofile&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">'.$lang->def('_VIEW_PROFILE').'</a>';
		// */

		$sender = '<div class="forum_author">'
			.$author['actions']
			.$author['display_name']
			.'</div>'
			.(isset($authors_info[$m_author]) ? '<div class="forum_level">'.$authors_info[$m_author]['level'].'</div>' : '')
			.'<br/>'
			.'<div class="forum_numpost">'.$lang->def('_NUMPOST').' : '.(isset($authors_info[$m_author]['num_post']) ? $authors_info[$m_author]['num_post'] : 0 ).'</div>'
			.'<br />'
			.'<span class="glyphicon glyphicon-user"></span> &nbsp;'
			.'<a href="index.php?modname=public_forum&amp;op=viewprofile&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'&amp;idThread='.$id_thread.'">'
			.'<span>'.$lang->def('_VIEW_PROFILE').'</span>'
			.'</a>';

		// msg info
		$msgtext = '';
		if ($counter == $first_unread_message)
			$msgtext .= '<a name="firstunread"></a><div class="forum_post_posted">';
		else
		$msgtext .= '<div class="forum_post_posted">';
		
		$msgtext .= $lang->def('_DATE').' : '.Format::date($message_info['posted'])
			.' ( '.loadDistance($message_info['posted']).' )'
			.'</div>';
		if($message_info['locked']) {

			$msgtext .= '<div class="forum_post_locked">'.$lang->def('_LOCKEDMESS').'</div>';
		} else {
			if($message_info['attach'] != '') {

				$msgtext .= '<div class="forum_post_attach">'
					.'<a href="index.php?modname=public_forum&amp;op=download&amp;id='.$id_message.'">'
					.$lang->def('_ATTACHMENT').' : '
					.'<img src="'.getPathImage('fw').mimeDetect($message_info['attach']).'" alt="'.$lang->def('_ATTACHMENT').'" /></a>'
					.'</div>';
			}
			$msgtext .= '<div class="forum_post_title">'.$lang->def('_SUBJECT').' : '.$message_info['title'].'</div>';
			$msgtext .= '<div class="forum_post_text">'
						.str_replace('[quote]', '<blockquote class="forum_quote">', str_replace('[/quote]', '</blockquote>', $message_info['textof']))
						.'</div>';

			if($message_info['modified_by'] != 0) {

				$modify_by = $message_info['modified_by'];
				$msgtext .= '<div class="forum_post_modified_by">'
						.$lang->def('_MODIFY_BY').' : '
						.( isset($authors_names[$m_author])
							?( $authors_names[$modify_by][ACL_INFO_LASTNAME].$authors_names[$modify_by][ACL_INFO_FIRSTNAME] == '' ?
								$acl_man->relativeId($authors_names[$modify_by][ACL_INFO_USERID]) :
								$authors_names[$modify_by][ACL_INFO_LASTNAME].' '.$authors_names[$modify_by][ACL_INFO_FIRSTNAME] )
							: $lang->def('_UNKNOWN_AUTHOR')
						)
						.' '.$lang->def('_ON').' : '
						.Format::date($message_info['modified_by_on'])
						.'</div>';
			}

			if(isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_SIGNATURE] != '') {
				$msgtext .= '<div class="forum_post_sign_separator"></div>'
					.'<div class="forum_post_sign">'
					.$authors_names[$m_author][ACL_INFO_SIGNATURE]
					.'</div>';
			}
		}
		$content = array($sender, $msgtext);
		$tb->addBody($content);

		// some action that you can do with this message
		$action = '<div class="btn-group hidden-xs pull-right">';
		if($moderate || $mod_perm) {
			if($message_info['locked']) {
				$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.' title="'.$lang->def('_DEMODERATE').' : '.strip_tags($message_info['title']).'">
											<span class="glyphicon glyphicon-ban-circle"></span> &nbsp;
											<span>'.$lang->def('_DEMODERATE').'</span>
										</a>';
				// $action .= '<li><a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
				// 		.'title="'.$lang->def('_DEMODERATE').' : '.strip_tags($message_info['title']).'">'
				// 	.'<img src="'.getPathImage().'standard/demoderate.png" alt="'.$lang->def('_DEMODERATE').' : '.strip_tags($message_info['title']).'" /> '
				// 	.$lang->def('_DEMODERATE').'</a></li>';
			} else {
				$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" title="'.$lang->def('_MODERATE').' : '.strip_tags($message_info['title']).'">
											<span class="glyphicon glyphicon-ban-circle"></span> &nbsp;
											<span>'.$lang->def('_MODERATE').'</span>
										</a>';
				// $action .= '<li><a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
				// 		.'title="'.$lang->def('_MODERATE').' : '.strip_tags($message_info['title']).'">'
				// 	.'<img src="'.getPathImage().'standard/moderate.png" alt="'.$lang->def('_MODERATE').' : '.strip_tags($message_info['title']).'" /> '
				// 	.$lang->def('_MODERATE').'</a></li>';
			}
		}
		if(!$locked_t && !$locked_f && !$message_info['locked'] && $write_perm) {
			$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" title="'.$lang->def('_REPLY').' : '.strip_tags($message_info['title']).'">
										<span class="glyphicon glyphicon-comment"></span> &nbsp;
										<span>'.$lang->def('_QUOTE').'</span>
									</a>';
			// $action .= '<li><a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
			// 		.'title="'.$lang->def('_REPLY').' : '.strip_tags($message_info['title']).'">'
			// 	.'<img src="'.getPathImage().'standard/reply.png" alt="'.$lang->def('_REPLY').' : '.strip_tags($message_info['title']).'" /> '
			// 	.$lang->def('_QUOTE').'</a></li>';
		}
		if($moderate || $mod_perm || ($m_author == getLogUserId()) ) {
			$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=modmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" title="'.$lang->def('_MOD_MESSAGE').' : '.strip_tags($message_info['title']).'">
										<span class="glyphicon glyphicon-pencil"></span> &nbsp;
										<span>'.$lang->def('_MOD_MESSAGE').'</span>
									</a>
									<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=delmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" title="'.$lang->def('_DEL').' : '.strip_tags($message_info['title']).'">
										<span class="glyphicon glyphicon-remove"></span> &nbsp;
										<span>'.$lang->def('_DEL').'</span>
									</a>';

			// $action .= '<li><a href="index.php?modname=public_forum&amp;op=modmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
			// 		.'title="'.$lang->def('_MOD_MESSAGE').' : '.strip_tags($message_info['title']).'">'
			// 	.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'" /> '
			// 	.$lang->def('_MOD').'</a></li>'
			// 	.'<li><a href="index.php?modname=public_forum&amp;op=delmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
			// 		.'title="'.$lang->def('_DEL').' : '.strip_tags($message_info['title']).'">'
			// 	.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.strip_tags($message_info['title']).'" /> '
			// 	.$lang->def('_DEL').'</a></li>';
		}
		$action .= '</div>';
		$action .= '<div class="dropdown visible-xs pull-right">
								<button class="btn btn-default dropdown-toggle" type="button" id="messageActions-'.$id_message.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								  <span class="glyphicon glyphicon-cog"></span> &nbsp;
								  Azioni &nbsp;
								  <span class="caret"></span>
								</button>
								<ul class="dropdown-menu" aria-labelledby="messageActions-'.$id_message.'">';
		
		if($moderate || $mod_perm) {
			if($message_info['locked']) {
				$action .= '<li><a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-ban-circle"></span> &nbsp;
											<span>'.$lang->def('_MODERATE').'</span>
										</a></li>';
			} else {
				$action .= '<li><a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-ban-circle"></span> &nbsp;
											<span>'.$lang->def('_DEMODERATE').'</span>
										</a></li>';
			}
		}
		if(!$locked_t && !$locked_f && !$message_info['locked'] && $write_perm) {
			$action .= '<li><a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-comment"></span> &nbsp;
										<span>'.$lang->def('_QUOTE').'</span>
									</a></li>';
		}
		if($moderate || $mod_perm || ($m_author == getLogUserId()) ) {
			$action .= '<li><a href="index.php?modname=public_forum&amp;op=modmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-pencil"></span> &nbsp;
										<span>'.$lang->def('_MOD_MESSAGE').'</span>
									</a></li>
									<li><a href="index.php?modname=public_forum&amp;op=delmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-remove"></span> &nbsp;
										<span>'.$lang->def('_DEL').'</span>
									</a></li>';
		}

		$action .= '</ul></div>';
		//$tb->addBodyExpanded('<ul class="link_list_inline align-right">'.$action.'</ul>', 'forum_action');
		$tb->addBodyExpanded($action);

	}
	
	if($moderate || $mod_perm) {
					
		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=delmessage]');
	}
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'forum')
		// .'<div class="std_block">'
		// .Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search&amp;idThread='.$id_thread)
		// 	.'<div class="quick_search_form">'
		// 	.'<label for="search_arg">'.$lang->def('_SEARCH_LABEL').'</label> '
		// 	.Form::getInputTextfield(	'search_t',
		// 								'search_arg',
		// 								'search_arg',
		// 								'',
		// 								$lang->def('_SEARCH'), 255, '' )
		// 	.'<input class="search_b" type="submit" id="search_button" name="search_button" value="'.$lang->def('_SEARCH').'" />'
		// 	.'</div>'
		// .Form::closeForm(),
		, 'content');

	// NOTE: If notify request register it
	require_once($GLOBALS['where_framework'].'/lib/lib.usernotifier.php');

	$can_notify = usernotifier_getUserEventStatus(getLogUserId(), 'ForumNewResponse');

	if(isset($_GET['notify']) && $can_notify) {
		if(issetNotify('thread', $id_thread, getLogUserId())) {
			$re = unsetNotify('thread', $id_thread, getLogUserId());
			$is_notify = !$re;
		} else {
			$re = setNotify('thread', $id_thread, getLogUserId());
			$is_notify = $re;
		}
		if($re) $GLOBALS['page']->add(getResultUi($lang->def('_NOTIFY_CHANGE_STATUS_CORRECT')), 'content');
		else $GLOBALS['page']->add(getErrorUi($lang->def('_NOTIFY_CHANGE_STATUS_FAILED')), 'content');
	} elseif($can_notify) {
		$is_notify = issetNotify('thread', $id_thread, getLogUserId());
	}

	$text_inner = '<div class="btn-group col-lg-9 visible-lg">';
	if(!$locked_t && !$locked_f && $write_perm) {
		$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'" title="'.$lang->def('_REPLY').'">
											<span class="glyphicon glyphicon-plus-sign"></span>&nbsp;
											<span>'.$lang->def('_REPLY').'</span>
										</a>';
	}

	if($can_notify) {
		$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=message&amp;notify=1&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'" title="'.( !$is_notify ? $lang->def('_NOTIFY_ME_THREAD_TITLE') : $lang->def('_UNNOTIFY_ME_THREAD_TITLE') ).'">
											<span class="glyphicon glyphicon-star"></span>&nbsp;
											<span>'.( !$is_notify ? $lang->def('_NOTIFY_ME_THREAD') : $lang->def('_UNNOTIFY_ME_THREAD') ).'</span>
										</a>';
	}

	if($mod_perm) {
		$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=modstatusthread&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'" title="'.( $locked_t ? $lang->def('_FREETHREAD') : $lang->def('_LOCKTHREAD') ).'">
											<span class="glyphicon glyphicon-lock"></span>&nbsp;
											<span>'.( $locked_t ? $lang->def('_FREETHREAD') : $lang->def('_LOCKTHREAD') ).'</span>
										</a>
										<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=changeerased&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'" title="'.( $erased_t ? $lang->def('_UNERASE') : $lang->def('_MODERATE') ).'">
											<span class="glyphicon glyphicon-'.( $erased_t ? 'lock' : 'ban-circle' ).'"></span>&nbsp;
											<span>'.( $erased_t ? $lang->def('_UNERASE') : $lang->def('_MODERATE') ).'</span>
										</a>';
	}

	if (checkPublicForumPerm('moderate', $id_forum) || checkPerm('mod', true)) {
		if ($is_important) {
			$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'&amp;important=2" title="'.$lang->def('_SET_NOT_IMPORTANT_THREAD').'">
												<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;
												<span>'.$lang->def('_SET_NOT_IMPORTANT_THREAD').'</span>
											</a>';
		} else {
			$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'&amp;important=1" title="'.$lang->def('_MARK_AS_IMPORTANT').'">
												<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;
												<span>'.$lang->def('_MARK_AS_IMPORTANT').'</span>
											</a>';
		}
	}

	$text_inner .= '</div>';
	$text_inner .= '<div class="dropdown col-xs-5 col-sm-4 hidden-lg pull-left">
										<button class="btn btn-default dropdown-toggle" type="button" id="tableActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										  <span class="glyphicon glyphicon-cog"></span> &nbsp;
										  Operazioni &nbsp;
										  <span class="caret"></span>
										</button>
										<ul class="dropdown-menu dropdown-menu-left" aria-labelledby="tableActions">';

	if(!$locked_t && !$locked_f && $write_perm) {
		$text_inner .= '<li><a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-plus-sign"></span>&nbsp;
											<span>'.$lang->def('_REPLY').'</span>
										</a></li>';
	}

	if($can_notify) {
		$text_inner .= '<li><a href="index.php?modname=public_forum&amp;op=message&amp;notify=1&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-star"></span>&nbsp;
											<span>'.( !$is_notify ? $lang->def('_NOTIFY_ME_THREAD') : $lang->def('_UNNOTIFY_ME_THREAD') ).'</span>
										</a></li>';
	}

	if($mod_perm) {
		$text_inner .= '<li><a href="index.php?modname=public_forum&amp;op=modstatusthread&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-lock"></span>&nbsp;
											<span>'.( $locked_t ? $lang->def('_FREETHREAD') : $lang->def('_LOCKTHREAD') ).'</span>
										</a></li>
										<li><a href="index.php?modname=public_forum&amp;op=changeerased&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-'.( $erased_t ? 'lock' : 'ban-circle' ).'"></span>&nbsp;
											<span>'.( $erased_t ? $lang->def('_UNERASE') : $lang->def('_MODERATE') ).'</span>
										</a></li>';
	}

	if (checkPublicForumPerm('moderate', $id_forum) || checkPerm('mod', true)) {
		if ($is_important) {
			$text_inner .= '<li><a href="index.php?modname=public_forum&op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'&amp;important=2">
												<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;
												<span>'.$lang->def('_SET_NOT_IMPORTANT_THREAD').'</span>
											</a></li>';
		} else {
			$text_inner .= '<li><a href="index.php?modname=public_forum&op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'&amp;important=1">
												<span class="glyphicon glyphicon-warning-sign"></span>&nbsp;
												<span>'.$lang->def('_MARK_AS_IMPORTANT').'</span>
											</a></li>';
		}
	}

	$text_inner .= '</ul>
								</div>
								<div class="col-xs-7 col-sm-6 col-md-4 col-lg-3 pull-right">'
								.Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search')
								.'<div class="input-group">'
								.Form::getSearchInputTextfield('', '', '', 'Cerca argomento...', '', '', '', '')
								.'<div class="input-group-btn"><button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-search"></span></button></div>'
								.'</div>'
								.Form::closeForm()
								.'</div>';

	if($text_inner != '') $tb->addActionAdd($text_inner);
	$GLOBALS['page']->add($tb->getNavBar($ini, $tot_message), 'content');
	$GLOBALS['page']->add($tb->getTable(), 'content');
	
	$GLOBALS['page']->add(
		$tb->getNavBar($ini, $tot_message)
		.'</div>', 'content');
}

//---------------------------------------------------------------------------//

function moderatemessage() {
	list( $id_thread, $lock ) = sql_fetch_row(sql_query("
	SELECT idThread, locked
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE idMessage = '".(int)$_GET['idMessage']."'"));
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	if(!checkPublicForumPerm('moderate', $id_forum) && !checkPerm('mod', true)) die("You can't access");
	
	if($lock == 1) $new_status = 0;
	else $new_status = 1;

	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forummessage
	SET locked = '$new_status'
	WHERE idMessage = '".(int)$_GET['idMessage']."'");

	Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&ini='.$_GET['ini']);
}

function modstatusthread() {
	$id_thread 		= importVar('idThread', true, 0);
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	if(!checkPublicForumPerm('moderate', $id_forum) && !checkPerm('mod', true)) die("You can't access");
	
	list( $idF, $lock ) = sql_fetch_row(sql_query("
	SELECT idForum, locked
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread ."'"));

	if($lock == 1) $new_status = 0;
	else $new_status = 1;

	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forumthread
	SET locked = '$new_status'
	WHERE idThread = '".$id_thread ."'");

	Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&ini='.$_GET['ini']);
}

function changeerase() {
	$id_thread 		= importVar('idThread', true, 0);
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	if(!checkPublicForumPerm('moderate', $id_forum) && !checkPerm('mod', true)) die("You can't access");
	
	list( $idF, $erased ) = sql_fetch_row(sql_query("
	SELECT idForum, erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'"));

	if($erased == 1) $new_status = 0;
	else $new_status = 1;

	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forumthread
	SET erased = '$new_status'
	WHERE idThread = '".$id_thread."'");

	Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&ini='.$_GET['ini']);
}

//---------------------------------------------------------------------------//

function showMessageForAdd($id_thread, $how_much) {

	require_once(_base_.'/lib/lib.table.php');
	$lang =& DoceboLanguage::createInstance('forum', 'lms');

	$acl_man 	=& Docebo::user()->getAclManager();

	$tb = new Table(Get::sett('visuItem'), $lang->def('_CAPTION_FORUM_MESSAGE_ADD'), $lang->def('_CAPTION_FORUM_MESSAGE_ADD'));

	// Find post
	$messages 		= array();
	$authors 		= array();
	$authors_names	= array();
	$authors_info	= array();
	$re_message = sql_query("
	SELECT idMessage, posted, title, textof, attach, locked, author, modified_by
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE idThread = '".$id_thread."'
	ORDER BY posted DESC
	LIMIT 0, ".$how_much);
	while($record = mysql_fetch_assoc($re_message)) {

		$messages[$record['idMessage']] 	= $record;
		$authors[$record['author']] 		= $record['author'];
		if($record['modified_by'] != 0) {
			$authors[$record['modified_by']] 	= $record['modified_by'];
		}
	}
	$authors_names =& $acl_man->getUsers($authors);
	$level_name = CourseLevel::getLevels();
	
	// Retriving level and number of post of th authors
	$re_num_post = sql_query("
	SELECT u.idUser, u.level, COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_forummessage AS m, ".$GLOBALS['prefix_lms']."_courseuser AS u
	WHERE m.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND m.author = u.idUser AND m.author IN ( ".implode($authors, ',')." )
	GROUP BY u.idUser, u.level");
	while( list($id_u, $level_u, $num_post_a) = sql_fetch_row($re_num_post) ) {

		$authors_info[$id_u] = array( 'num_post' => $num_post_a, 'level' => $level_name[$level_u] );
	}
	$type_h = array('forum_sender', 'forum_text');
	$cont_h = array($lang->def('_AUTHOR'), $lang->def('_TEXTOF'));
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	// Compose messagges display
	$path = $GLOBALS['where_files_relative'].'/appCore/'.Get::sett('pathphoto');
	while(list($id_message, $message_info) = each($messages)) {

		// sender info
		$m_author = $message_info['author'];

		if(isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_AVATAR] != '')
			$img_size = @getimagesize($path.$authors_names[$m_author][ACL_INFO_AVATAR]);

		$sender = '<div class="forum_author">'
			.( isset($authors_names[$m_author])
				?( $authors_names[$m_author][ACL_INFO_LASTNAME].$authors_names[$m_author][ACL_INFO_FIRSTNAME] == '' ?
					$acl_man->relativeId($authors_names[$m_author][ACL_INFO_USERID]) :
					$authors_names[$m_author][ACL_INFO_LASTNAME].' '.$authors_names[$m_author][ACL_INFO_FIRSTNAME] )
				: $lang->def('_UNKNOWN_AUTHOR')
			)
			.'</div>'
			.( isset($authors_info[$m_author])
				? '<div class="forum_level">'.$lang->def('_LEVEL').' : '.$authors_info[$m_author]['level'].'</div>' : '' )
			.( isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_AVATAR] != ''
				? '<img class="forum_avatar'.( $img_size[0] > 150 || $img_size[1] > 150 ? ' image_limit' : '' ).'" src="'.$path.$authors_names[$m_author][ACL_INFO_AVATAR].'" alt="'.$lang->def('_AVATAR').'" />'
				: '')
			.'<div class="forum_numpost">'.$lang->def('_NUMPOST').' : '
			.( isset($authors_info[$m_author]['num_post'])
				? $authors_info[$m_author]['num_post']
				: 0 )
			.'</div>'
			.'<img src="'.getPathImage().'standard/identity.png" alt="&gt;" />&nbsp;'
			.'<a href="index.php?modname=public_forum&amp;op=viewprofile&amp;idMessage='.$id_message.'">'.$lang->def('_VIEW_PROFILE').'</a>';

		// msg info
		$msgtext = '';

		$msgtext .= '<div class="forum_post_posted">'
			.$lang->def('_DATE').' : '.Format::date($message_info['posted'])
			.' ( '.loadDistance($message_info['posted']).' )'
			.'</div>';
		if($message_info['locked']) {

			$msgtext .= '<div class="forum_post_locked">'.$lang->def('_LOCKEDMESS').'</div>';
		} else {
			$msgtext .= '<div class="forum_post_title">'.$lang->def('_SUBJECT').' : '.$message_info['title'].'</div>';

			$msgtext .= '<div class="forum_post_text">'
				.str_replace('[quote]', '<blockquote class="forum_quote">', str_replace('[/quote]', '</blockquote>', $message_info['textof']))
				.'</div>';

			if($message_info['modified_by'] != 0) {

				$modify_by = $message_info['modified_by'];
				$msgtext .= '<div class="forum_post_modified_by">'
						.$lang->def('_MODIFY_BY').' : '
						.( isset($authors_names[$modify_by])
							?( $authors_names[$modify_by][ACL_INFO_LASTNAME].$authors_names[$modify_by][ACL_INFO_FIRSTNAME] == '' ?
								$acl_man->relativeId($authors_names[$modify_by][ACL_INFO_USERID]) :
								$authors_names[$modify_by][ACL_INFO_LASTNAME].' '.$authors_names[$modify_by][ACL_INFO_FIRSTNAME] )
							: $lang->def('_UNKNOWN_AUTHOR')
						).'</div>';
			}
			if(isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_SIGNATURE] != '') {
				$msgtext .= '<div class="forum_post_sign_separator"></div>'
					.'<div class="forum_post_sign">'
					.$authors_names[$m_author][ACL_INFO_SIGNATURE]
					.'</div>';
			}
		}
		$content = array($sender, $msgtext);
		$tb->addBody($content);
	}
	$GLOBALS['page']->add($tb->getTable(), 'content');
}

function addmessage() {
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum', 'lms');
	$id_thread 		= importVar('idThread', true, 0);
	$id_message 	= importVar('idMessage', true, 0);
	$ini = importVar('ini');
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	if (!checkPublicForumPerm('write', $id_forum))
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	$mod_perm	= checkPerm('mod', true);
	$acl_man 	=& Docebo::user()->getAclManager();

	// Some info about forum and thread
	$thread_query = "
	SELECT idForum, title , locked, erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'";
	list($id_forum, $thread_title, $locked_t, $erased_t) = sql_fetch_row(sql_query($thread_query));
	$forum_query = "
	SELECT title, locked
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".$id_forum."'";
	list($forum_title, $locked_f) = sql_fetch_row(sql_query($forum_query));

	$page_title = array(
		'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
		'index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum => $forum_title,
		'index.php?modname=public_forum&amp;op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini => $thread_title,
		$lang->def('_REPLY')
	);
	if(($erased_t || $locked_t) && !$mod_perm && !$moderate) {

		$GLOBALS['page']->add(
			getTitleArea($page_title, 'forum')
			.'<div class="std_block">'
			.$lang->def('_CANNOTENTER')
			.'</div>', 'content');
		return;
	}
	// retrive info about quoting
	if($id_message != 0) {

		$message_query = "
		SELECT title, textof, locked, author
		FROM ".$GLOBALS['prefix_lms']."_forummessage
		WHERE idMessage = '".$id_message."'";
		list($m_title, $m_textof, $m_locked, $author) = sql_fetch_row(sql_query($message_query));
		if ($m_locked) {
            unset($m_title, $m_textof);
            $id_message=0;
        }
	}
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'forum')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=public_forum&amp;op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini, $lang->def('_BACK'))
		.Form::openForm('form_forum', 'index.php?modname=public_forum&amp;op=insmessage', false, false, 'multipart/form-data')
		.Form::openElementSpace()

		.Form::getHidden('idThread', 'idThread', $id_thread)
		.Form::getHidden('idMessage', 'idMessage', $id_message)
		.Form::getHidden('ini', 'ini', $ini)
		.Form::getTextfield($lang->def('_SUBJECT'), 'title', 'title', 255, ($id_message != '' ? $lang->def('_RE').' '.$m_title : $thread_title ))
		.Form::getTextarea($lang->def('_TEXTOF'), 'textof', 'textof', ($id_message != '' ? '<em>'.$lang->def('_WRITTED_BY').': '.$acl_man->getUserName($author).'</em><br /><br />[quote]'.$m_textof.'[/quote]' : '' ))
	, 'content');
	if(checkPublicForumPerm('upload', $id_forum)) {

		$GLOBALS['page']->add(Form::getFilefield($lang->def('_UPLOAD'), 'attach', 'attach'), 'content');
	}
	$GLOBALS['page']->add(
		Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('post_thread', 'post_thread', $lang->def('_SEND'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
	, 'content');
	showMessageForAdd($id_thread, 3);
	$GLOBALS['page']->add(
		Form::openButtonSpace()
		.Form::getButton('post_thread_2', 'post_thread', $lang->def('_SEND'))
		.Form::getButton('undo_2', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function insmessage() {
	$id_thread 	= importVar('idThread', true, 0);
	$id_message 	= importVar('idMessage', true, 0);
	$ini 	= importVar('ini');
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	if(!checkPublicForumPerm('write', $id_forum))
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	$mod_perm	= checkPerm('mod', true);

	if(isset($_POST['undo'])) Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini);

	$lang =& DoceboLanguage::createInstance('forum');

	// Some info about forum and thread
	list($id_forum, $thread_title, $locked_t, $erased_t) = sql_fetch_row(sql_query("
	SELECT idForum, title, locked, erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'"));
	$forum_query = "
	SELECT title
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".$id_forum."'";
	list($forum_title) = sql_fetch_row(sql_query($forum_query));

	$locked_f = false;
	if(!checkPublicForumPerm('moderate', $id_forum)) {

		$query_view_forum = "
		SELECT idMember, locked
		FROM ".$GLOBALS['prefix_lms']."_forum AS f L
		WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND f.idForum = '".$id_forum."'";
		$re_forum = sql_query($query_view_forum);
		while(list($id_m, $lock_s, $erase_s) = sql_fetch_row($re_forum)) {

			$locked_f = $lock_s;
			if($id_m != NULL) $members[] = $id_m;
		}
	}
	$continue = false;
	if(!isset($members)) $continue = true;
	else {
		$acl 	=& Docebo::user()->getAcl();
		$all_user_idst = $acl->getSTGroupsST(getLogUserId());
		$all_user_idst[] = getLogUserId();

		$can_access = array();
		$can_access = array_intersect($members, $all_user_idst);
		if(!empty($can_access)) $continue = true;
	}
	if(!$continue) Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini.'&amp;result=err_cannotsee');
	if($locked_f || $locked_t ||$erased_t && (!$mod_perm && !$moderate)) {
		Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini.'&amp;result=err_lock');
	}

	if($_POST['title'] == '') {
		if($_POST['textof'] != '') {

			$_POST['title'] = substr(strip_tags($_POST['textof']), 0, 50).( count($_POST['textof']) > 50 ? '...' : '' );
		} else {

			$_POST['title'] = $lang->def('_NOTITLE');
		}
	}

	$now = date("Y-m-d H:i:s");

	//save attachment
	$name_file = '';
	if($_FILES['attach']['name'] != '' && checkPublicForumPerm('upload', $id_forum) ) {
		$name_file = save_file($_FILES['attach']);
	}
	$answer_tree = '';
	if($id_message != 0) {

		list($answer_tree) = sql_fetch_row(sql_query("
		SELECT answer_tree
		FROM ".$GLOBALS['prefix_lms']."_forummessage
		WHERE idMessage = '".$id_message."'"));
	}
	$answer_tree .= '/'.$now;

	$ins_mess_query = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_forummessage
	( idThread, idCourse, title, textof, author, posted, answer_tree, attach ) VALUES
	( 	'".$id_thread."',
		'".(int)PUBLIC_FORUM_COURSE_ID."',
		'".$_POST['title']."',
		'".$_POST['textof']."',
		'".getLogUserId()."',
		'".$now."',
		'".$answer_tree."',
		'".addslashes($name_file)."' )";
	if(!sql_query($ins_mess_query)) {

		delete_file($name_file);
		Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini.'&amp;result=err_ins');
	}
	list($new_id_message) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forum
	SET num_post = num_post + 1,
		last_post = '".$new_id_message."'
	WHERE idForum = '".$id_forum."'");

	sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_forumthread
	SET num_post = num_post + 1,
		last_post = '".$new_id_message."'
	WHERE idThread = '".$id_thread."'");

	// launch notify
	require_once(_base_.'/lib/lib.eventmanager.php');

	$msg_composer = new EventMessageComposer();

	$msg_composer->setSubjectLangText('email', '_SUBJECT_NOTIFY_MESSAGE', false);
	$msg_composer->setBodyLangText('email', '_NEW_MESSAGE_INSERT_IN_THREAD', array(	'[url]' => Get::sett('url'),
																		'[course]' => PUBLIC_FORUM_COURSE_NAME,
																		'[forum_title]' => $forum_title,
																		'[thread_title]' => $_POST['title'] ) );

	$msg_composer->setBodyLangText('sms', '_NEW_MESSAGE_INSERT_IN_THREAD_SMS', array(	'[url]' => Get::sett('url'),
																		'[course]' => PUBLIC_FORUM_COURSE_NAME,
																		'[forum_title]' => $forum_title,
																		'[thread_title]' => $_POST['title'] ) );

	launchNotify('thread', $id_thread, $lang->def('_NEW_MESSAGE'), $msg_composer);

	Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini.'&amp;result=ok');
}

//---------------------------------------------------------------------------//

function modmessage() {
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum', 'lms');
	$id_message 	= importVar('idMessage', true, 0);
	$ini 			= importVar('ini');

	$acl_man 	=& Docebo::user()->getAclManager();

	// retrive info about message
	$mess_query = "
	SELECT idThread, title, textof, author
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE  idMessage = '".$id_message."'";
	list($id_thread, $title, $textof, $author) = sql_fetch_row(sql_query($mess_query));
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	if (!checkPublicForumPerm('view', $id_forum))
		die("You can't access!'");
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	$mod_perm	= checkPerm('mod', true);	
	
	if(!$moderate && !$mod_perm && ($author != getLogUserId()) ) die("You can't access");

	// Some info about forum and thread
	$thread_query = "
	SELECT idForum, title , locked, erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'";
	list($id_forum, $thread_title, $locked_t, $erased_t) = sql_fetch_row(sql_query($thread_query));
	$forum_query = "
	SELECT title, locked
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".$id_forum."'";
	list($forum_title, $locked_f) = sql_fetch_row(sql_query($forum_query));

	$page_title = array(
		'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
		'index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum => $forum_title,
		'index.php?modname=public_forum&amp;op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini => $thread_title,
		$lang->def('_MOD_MESSAGE')
	);
	if($erased_t && !$mod_perm && !$moderate) {

		$GLOBALS['page']->add(
			getTitleArea($page_title, 'forum')
			.'<div class="std_block">'
			.$lang->def('_CANNOTENTER')
			.'</div>', 'content');
		return;
	}

	$GLOBALS['page']->add(
		getTitleArea($page_title, 'forum')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=public_forum&amp;op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini, $lang->def('_BACK'))
		.Form::openForm('form_forum', 'index.php?modname=public_forum&amp;op=upmessage', false, false, 'multipart/form-data')
		.Form::openElementSpace()

		.Form::getHidden('idMessage', 'idMessage', $id_message)
		.Form::getHidden('ini', 'ini', $ini)
		.Form::getTextfield($lang->def('_SUBJECT'), 'title', 'title', 255, $title)
		.Form::getTextarea($lang->def('_TEXTOF'), 'textof', 'textof', $textof)
	, 'content');
	if(checkPublicForumPerm('upload', $id_forum)) {

		$GLOBALS['page']->add(Form::getFilefield($lang->def('_UPLOAD'), 'attach', 'attach'), 'content');
	}
	$GLOBALS['page']->add(
		Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('post_thread', 'post_thread', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
	, 'content');
	showMessageForAdd($id_thread, 3);
	$GLOBALS['page']->add(
		Form::openButtonSpace()
		.Form::getButton('post_thread_2', 'post_thread', $lang->def('_SAVE'))
		.Form::getButton('undo_2', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function upmessage() {
	$id_message 	= importVar('idMessage', true, 0);
	$ini 	= importVar('ini');

	$lang =& DoceboLanguage::createInstance('forum');

	// retrive info about message
	$mess_query = "
	SELECT idThread, author, attach, generator
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE  idMessage = '".$id_message."'";
	list($id_thread, $author, $attach, $is_generator) = sql_fetch_row(sql_query($mess_query));
	if(isset($_POST['undo'])) Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini);

	list($id_forum, $locked_t, $erased_t) = sql_fetch_row(sql_query("
	SELECT idForum, locked, erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'"));
	
	if (!checkPublicForumPerm('view', $id_forum))
		die("You can't access!'");
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	$mod_perm	= checkPerm('mod', true);
	
	if(!$moderate && !$mod_perm && ($author != getLogUserId()) ) die("You can't access");
	
	if($locked_t ||$erased_t && (!$mod_perm && !$moderate)) {
		Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini.'&amp;result=err_lock');
	}
	if($_POST['title'] == '') $_POST['title'] = substr(strip_tags($_POST['textof']), 0, 50).'...';

	$now = date("Y-m-d H:i:s");

	//save attachment
	$name_file = $attach;
	if($_FILES['attach']['name'] != '' && checkPublicForumPerm('upload', $id_forum) ) {

		delete_file($attach);
		$name_file = save_file($_FILES['attach']);
	}
	$upd_mess_query = "
	UPDATE ".$GLOBALS['prefix_lms']."_forummessage
	SET title = '".$_POST['title']."',
		textof = '".$_POST['textof']."',
		attach = '".$name_file."',
		modified_by = '".getLogUserId()."',
		modified_by_on = '".$now."'
	WHERE idMessage = '".$id_message."' AND idCourse = '".PUBLIC_FORUM_COURSE_ID."'";
	if(!sql_query($upd_mess_query)) {

		delete_file($name_file);
		Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini.'&amp;result=err_ins');
	}

	if($is_generator) {
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forumthread
		SET title = '".$_POST['title']."'
		WHERE idThread = '".$id_thread."'");
	}
	Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini.'&amp;result=ok');
}

//---------------------------------------------------------------------------//

function delmessage() {
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum', 'lms');

	$id_message 	= importVar('idMessage', true, 0);
	$ini 	= importVar('ini');

	$mess_query = "
	SELECT idThread, title, textof, author, attach, answer_tree
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE idMessage = '".$id_message."'";
	list($id_thread, $title, $textof, $author, $file, $answer_tree) = sql_fetch_row(sql_query($mess_query));

	$thread_query = "
	SELECT idForum, title, num_post, last_post
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'";
	list($id_forum, $thread_title, $num_post, $last_post) = sql_fetch_row(sql_query($thread_query));
	
	if(!checkPublicForumPerm('view', $id_forum))
		die("You can't access");
	
	$moderate 		= checkPublicForumPerm('moderate', $id_forum);
	$mod_perm		= checkPerm('mod', true);
	
	if(!$moderate && !$mod_perm && ($author != getLogUserId()) ) die("You can't access");
	
	$forum_query = "
	SELECT title
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".$id_forum."'";
	list($forum_title) = sql_fetch_row(sql_query($forum_query));

	if(isset($_POST['undo'])) Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;ini='.$ini);
	if(isset($_POST['confirm']) || Get::req('confirm', DOTY_INT)) {

		$new_answer_tree = substr($answer_tree, 0, -21);
		if(!sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forummessage
		SET answer_tree = CONCAT( '$new_answer_tree', SUBSTRING( answer_tree FROM ".strlen($answer_tree)." ) )
		WHERE answer_tree LIKE '".$answer_tree."/%' AND idCourse = '".PUBLIC_FORUM_COURSE_ID."'"))
			Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;result=err_del');

		if(!sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forum
		SET num_post = num_post - 1
			".( $num_post == 0 ? " ,num_thread = num_thread - 1 " : " " )."
		WHERE idForum = '".$id_forum."'"))
			Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;result=err_del');

		if(($num_post != 0) && ($last_post == $id_message)) {

			$query_text = "
			SELECT idMessage
			FROM ".$GLOBALS['prefix_lms']."_forummessage
			WHERE idThread = '".$id_thread."'
			ORDER BY posted DESC";
			$re = sql_query($query_text);
			list($id_new, $post) = sql_fetch_row($re);
		}
		if($num_post == 0) {

			if(!sql_query("
			DELETE FROM ".$GLOBALS['prefix_lms']."_forumthread
			WHERE idThread = '".$id_thread."'"))
				Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;result=err_del');
			unsetNotify('thread', $id_thread);
		} else {

			if(!sql_query("
			UPDATE ".$GLOBALS['prefix_lms']."_forumthread
			SET num_post = num_post - 1 "
				.( ($last_post == $id_message) ? " , last_post = '".$id_new."'" : '' )."
			WHERE idThread = '".$id_thread."'"))
				Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;result=err_del');
		}
		delete_file($file);

		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_forummessage
		WHERE idMessage = '".$id_message."' AND idCourse = '".PUBLIC_FORUM_COURSE_ID."'"))
			Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;result=err_del');

		if($num_post == 0) Util::jump_to('index.php?modname=public_forum&op=thread&idForum='.$id_forum.'&amp;result=ok');
		else Util::jump_to('index.php?modname=public_forum&op=message&idThread='.$id_thread.'&amp;result=ok');
	} else {

		$page_title = array(
			'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
			'index.php?modname=public_forum&amp;op=thread&amp;idForum='.$id_forum => $forum_title,
			'index.php?modname=public_forum&amp;op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini => $thread_title,
			$lang->def('_DEL')
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'forum')
			.'<div class="std_block">'
			.Form::openForm('del_thread', 'index.php?modname=public_forum&amp;op=delmessage')
			.Form::getHidden('idMessage', 'idMessage', $id_message)
			.Form::getHidden('ini', 'ini', $ini)
			.getDeleteUi(
				$lang->def('_AREYOUSURE'),
				'<span>'.$lang->def('_SUBJECT').' :</span> '.$title.'<br />'
				.$textof,
				false,
				'confirm',
				'undo' )
			.Form::closeForm()
			.'</div>', 'content');
	}
}

//---------------------------------------------------------------------------//

function viewprofile() {
	//checkPerm('view');

	require_once(_base_.'/lib/lib.usermanager.php');
	$lang =& DoceboLanguage::createInstance('forum');

	$id_message = importVar('idMessage');
	$ini = importVar('ini', true, 1);
	$idThread = importVar('idThread', true, 1);

	list($id_thread, $idst_user) = sql_fetch_row(sql_query("
	SELECT idThread, author
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE idMessage = '".$id_message."'"));
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$idThread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	if(!checkPublicForumPerm('view', $id_forum))
		die("You can't access");
	
	require_once($GLOBALS['where_lms'].'/lib/lib.lms_user_profile.php');

	$lang =& DoceboLanguage::createInstance('profile', 'framework');

	$profile = new LmsUserProfile( $idst_user );
	$profile->init('profile', 'framework', 'modname=public_forum&op=viewprofile&idMessage='.$id_message.'&ini='.$ini, 'ap');

	$GLOBALS['page']->add(
		$profile->getTitleArea()

		.$profile->getHead()

		.$profile->performAction()

		.forumBackUrl()

		.$profile->getFooter()
	, 'content');
}

//---------------------------------------------------------------------------//

function forumBackUrl()
	{
		$lang =& DoceboLanguage::createInstance('profile', 'framework');
		$id_user = importVar('id_user', true, 0);
		$ap = importVar('ap', true, 0);
		$ini = importVar('ini',true, 0);
		$id_thread = importVar('idThread', true, 0);
		$id_message = importVar('idMessage', true, 0);
		if ($id_user === 0)
			return getBackUi('index.php?modname=public_forum&amp;op=message&amp;idThread='.$id_thread.'&amp;ini='.$ini.'&amp;idMessage='.$id_message.'', '<< '.$lang->def('_BACK').'');
		return getBackUi('index.php?modname=public_forum&amp;op=viewprofile&amp;idThread='.$id_thread.'&amp;ini='.$ini.'&amp;idMessage='.$id_message.'', '<< '.$lang->def('_BACK').'');
	}
	
//---------------------------------------------------------------------------//

function forumsearch() {
	//checkPerm('view');

	if(isset($_POST['search_arg'])) {
		$_SESSION['forum']['search_arg'] = $_POST['search_arg'];
		$search_arg = importVar('search_arg');
	} else {
		$search_arg = $_SESSION['forum']['search_arg'];
	}
	$ord = importVar('ord');
	$mod_perm = checkPerm('mod', true);
	
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.navbar.php');
	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('forum');

	$acl_man 	=& Docebo::user()->getAclManager();

	if($mod_perm) {

		$query_view_forum = "
		SELECT DISTINCT idForum
		FROM ".$GLOBALS['prefix_lms']."_forum
		WHERE idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."'";
	} else {

		$acl 	=& Docebo::user()->getAcl();
		$all_user_idst = $acl->getSTGroupsST(getLogUserId());
		$all_user_idst[] = getLogUserId();

		$query_view_forum = "
		SELECT DISTINCT f.idForum
		FROM ".$GLOBALS['prefix_lms']."_forum AS f
		WHERE f.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' ";

	}
	$forums = array();
	$re_forum = sql_query($query_view_forum);
	while(list($id_f) = sql_fetch_row($re_forum)) {

		if (checkPublicForumPerm('view', $id_f) || checkPerm('mod', true))
			$forums[] = $id_f;
	}
	if(empty($forums)) {

		$page_title = array(
			'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
			$lang->def('_SEARCH_RESULT_FOR').' : '.$search_arg
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'forum')
			.'<div class="std_block">'
			.$lang->def('_NO_PLACEFORSEARCH')
			.'</div>', 'content');
	}
	$query_num_thread = "
	SELECT COUNT(DISTINCT t.idThread)
	FROM ".$GLOBALS['prefix_lms']."_forumthread AS t JOIN
			".$GLOBALS['prefix_lms']."_forummessage AS m
	WHERE t.idThread = m.idThread AND t.idForum IN ( ".implode($forums, ',')." )";
	
	if (isset($search_arg))
		$query_num_thread .= " AND ( m.title LIKE '%".$search_arg."%' OR m.textof LIKE '%".$search_arg."%' ) ";
	
	list($tot_thread) = sql_fetch_row(sql_query($query_num_thread));

	$jump_url = 'index.php?modname=public_forum&amp;op=search';
	$nav_bar 	= new NavBar('ini', Get::sett('visuItem'), $tot_thread, 'link');
	$nav_bar->setLink($jump_url.'&amp;ord='.$ord);
	$ini 		= $nav_bar->getSelectedElement();
	$ini_page	= $nav_bar->getSelectedPage();

	$query_thread = "
	SELECT DISTINCT t.idThread, t.idForum, t.author AS thread_author, t.posted, t.title, t.num_post, t.num_view, t.locked, t.erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread AS t JOIN
			".$GLOBALS['prefix_lms']."_forummessage AS m
	WHERE t.idThread = m.idThread AND t.idForum IN ( ".implode($forums, ',')." )
		AND ( m.title LIKE '%".$search_arg."%' OR m.textof LIKE '%".$search_arg."%' ) ";
	switch($ord) {
		case "obji"		: $query_thread .= " ORDER BY t.title DESC " ;	break;
		case "obj" 		: $query_thread .= " ORDER BY t.title " ;			break;
		case "authi"	: $query_thread .= " ORDER BY t.author DESC " ;	break;
		case "auth" 	: $query_thread .= " ORDER BY t.author " ;		break;
		case "posti" 	: $query_thread .= " ORDER BY m.posted " ;		break;
		case "post"		:
		default 		: {
			$ord = 'post';
			$query_thread .= " ORDER BY m.posted DESC " ;	break;
		}
	}
	$query_thread .= " LIMIT $ini, ".Get::sett('visuItem');
	$re_thread = sql_query($query_thread);

	$re_last_post = sql_query("
	SELECT m.idThread, t.author AS thread_author, m.posted, m.title, m.author  AS mess_author, m.generator
	FROM ".$GLOBALS['prefix_lms']."_forumthread AS t LEFT JOIN
		".$GLOBALS['prefix_lms']."_forummessage AS m ON ( t.last_post = m.idMessage )
	WHERE t.idForum IN ( ".implode($forums, ',')." )");
	while(list($idT_p, $id_ta, $posted, $title_p, $id_a, $is_gener) = sql_fetch_row($re_last_post)) {

		$last_authors[$id_ta] = $id_ta;
		if($posted !== NULL) {

			$last_post[$idT_p]['info'] = Format::date($posted).'<br />'.substr(strip_tags($title_p), 0, 15).' ...';
			$last_post[$idT_p]['author'] = $id_a;
			$last_authors[$id_a] = $id_a;
		}
	}
	if(isset($last_authors)) {
		$authors_names =& $acl_man->getUsers($last_authors);
	}

	$page_title = array(
		'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
		$lang->def('_SEARCH_RESULT_FOR').' : '.$search_arg
	);
	$GLOBALS['page']->add(
		 getTitleArea($page_title, 'forum')
		// .'<div class="std_block">'
		// .Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search')
		// .'<div class="quick_search_form">'
		// .'<label for="search_arg">'.$lang->def('_SEARCH_LABEL').'</label> '
		// .Form::getInputTextfield(	'search_t',
		// 							'search_arg',
		// 							'search_arg',
		// 							'',
		// 							$lang->def('_SEARCH'), 255, '' )
		// .'<input class="search_b" type="submit" id="search_button" name="search_button" value="'.$lang->def('_SEARCH').'" />'
		// .'</div>'
		// .Form::closeForm()
	, 'content');

	// $tb = new Table(Get::sett('visuItem'), $lang->def('_THREAD_CAPTION'), $lang->def('_THRAD_SUMMARY'));

	$threads_order = '<div class="dropdown pull-right">
										  <button class="btn btn-default dropdown-toggle" type="button" id="threadsOrder" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										    <span class="glyphicon glyphicon-filter"></span> &nbsp;
										    <span class="hidden-xs">Ordina &nbsp;</span>
										    <span class="caret"></span>
										  </button>
										  <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="threadOrder">
										    <li>
													<a href="'.$jump_url.'&amp;ord=posti">
														<span class="glyphicon glyphicon-sort-by-order"></span> &nbsp;
														<span>Data (crescente)</span>
													</a>
										    </li>
										    <li>
										    	<a href="'.$jump_url.'&amp;ord=post">
										    		<span class="glyphicon glyphicon-sort-by-order-alt"></span> &nbsp;
										    		<span>Data (decrescente)</span>
										    	</a>
										    </li>
										  </ul>
										</div>';

	$tb = new Table(Get::sett('visuItem'), $lang->def('_THREAD_CAPTION').$threads_order, $lang->def('_THRAD_SUMMARY'), 'thread-summary mobile-reverse');

	// $img_up 	= '<img src="'.getPathImage().'standard/ord_asc.png" alt="'.$lang->def('_ORD_ASC').'" />';
	// $img_down 	= '<img src="'.getPathImage().'standard/ord_desc.png" alt="'.$lang->def('_ORD_DESC').'" />';

	// $cont_h = array(
	// 	'<img src="'.getPathImage().'standard/msg_read.png" title="'.$lang->def('_FREET').'" alt="'.$lang->def('_FREE').'" />',
	// 	'<a href="'.$jump_url.'&amp;ord='.( $ord == 'obj' ? 'obji' : 'obj' ).'" title="'.$lang->def('_ORDER_BY').'">'
	// 		.( $ord == 'obj' ? $img_up : ( $ord == 'obji' ? $img_down : '' ) ).$lang->def('_THREAD').'</a>',
	// 	$lang->def('_NUMREPLY'),
	// 	'<a href="'.$jump_url.'&amp;ord='.( $ord == 'auth' ? 'authi' : 'auth' ).'" title="'.$lang->def('_ORDER_BY').'">'
	// 		.( $ord == 'auth' ? $img_up : ( $ord == 'authi' ? $img_down : '' ) ).$lang->def('_AUTHOR').'</a>',
	// 	$lang->def('_NUMVIEW'),
	// 	//$lang->def('_DATE'),
	// 	'<a href="'.$jump_url.'&amp;ord='.( $ord == 'post' ? 'posti' : 'post' ).'" title="'.$lang->def('_ORDER_BY').'">'
	// 		.( $ord == 'post' ? $img_up : ( $ord == 'posti' ? $img_down : '' ) ).$lang->def('_LASTPOST').'</a>'
	// );
	// $type_h = array('image', '', 'align_center', 'align_center', 'image',
	// //'align_center',
	// 'align_center');
	// if($mod_perm) {

	// 	$cont_h[] = '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" />';
	// 	$type_h[] = 'image';
	// 	$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />';
	// 	$type_h[] = 'image';
	// }

	$type_h = array(
		'hidden-xs text-center',
		'col-xs-12 col-sm-3 text-left',
		'col-xs-12 col-sm-2 text-left',
		'col-xs-12 col-sm-2 text-left',
		'col-xs-12 col-sm-3 text-left',
		'col-xs-12 col-sm-2 text-center clearfix'
	);

	$tb->setColsStyle($type_h);
	// $tb->addHead($cont_h);
	while(list($idT, $id_forum, $t_author, $posted, $title, $num_post, $num_view, $locked, $erased) = sql_fetch_row($re_thread)) {

		$c_css = '';
		// thread author
		$t_author = ( isset($authors_names[$t_author])
				?( $authors_names[$t_author][ACL_INFO_LASTNAME].$authors_names[$t_author][ACL_INFO_FIRSTNAME] == '' ?
					$acl_man->relativeId($authors_names[$t_author][ACL_INFO_USERID]) :
					$authors_names[$t_author][ACL_INFO_LASTNAME].' '.$authors_names[$t_author][ACL_INFO_FIRSTNAME] )
				: $lang->def('_UNKNOWN_AUTHOR')
			);
		// last post author
		if(isset($last_post[$idT])) {

			$author = $last_post[$idT]['author'];
			$last_mess_write = $last_post[$idT]['info'].' ( '.$lang->def('_BY').': <span class="mess_author">'
						.( isset($authors_names[$author])
							?( $authors_names[$author][ACL_INFO_LASTNAME].$authors_names[$author][ACL_INFO_FIRSTNAME] == '' ?
								$acl_man->relativeId($authors_names[$author][ACL_INFO_USERID]) :
								$authors_names[$author][ACL_INFO_LASTNAME].' '.$authors_names[$author][ACL_INFO_FIRSTNAME] )
							: $lang->def('_UNKNOWN_AUTHOR')
						).'</span> )';
		} else {
			$last_mess_write = $lang->def('_NONE');
		}
		// status of the thread
		// if($erased) {
		// 	$status = '<img src="'.getPathImage().'standard/cancel.png" alt="'.$lang->def('_FREE').'" />';
		// } elseif($locked) {
		// 	$status = '<img src="'.getPathImage().'standard/locked.png" alt="'.$lang->def('_LOCKED').'" />';
		// } elseif(isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT])) {

		// 	$status = '<img src="'.getPathImage().'standard/msg_unread.png" alt="'.$lang->def('_UNREAD').'" />';
		// 	$c_css = ' class="text_bold"';
		// } else {
		// 	$status = '<img src="'.getPathImage().'standard/msg_read.png" alt="'.$lang->def('_FREE').'" />';
		// }

		if($erased) {
			$status = '<span class="glyphicon glyphicon-trash" title="'.$lang->def('_FREE').'"></span>';
		} elseif($locked) {
			$status = '<span class="glyphicon glyphicon-lock" title="'.$lang->def('_LOCKED').'"></span>';
		} elseif(isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$idT])) {
			$status = '<span class="glyphicon glyphicon-folder-close" title="'.$lang->def('_UNREAD').'"></span>';
			$c_css = ' class="text_bold"';
		} else {
			$status = '<span class="glyphicon glyphicon-folder-open" title="'.$lang->def('_FREE').'"></span>';
		}

		$content = array($status);
		$content[] = ( $erased && !$mod_perm ?
					'<div class="forumErased">'.$lang->def('_OPERATION_SUCCESSFUL').'</div>' :
					'<a'.$c_css.' href="index.php?modname=public_forum&amp;op=searchmessage&amp;idThread='.$idT.'&amp;ini_thread='.$ini_page.'">'
					// .( $search_arg !== ''
					// 		? eregi_replace($search_arg, '<span class="filter_evidence">'.$search_arg.'</span>', $title)
					// 		: $title ).'</a>');
					.'<strong>'.$title.'</strong></a>');
		//$content[] = $num_post

		$content[] = '<p>Risposte: <span class="badge">'.$num_post.'</span></p>'
									.'<p>Visite: <span class="badge">'.$num_view.'</span></p>';

		$content[] = $t_author;
		//$content[] = $num_view;
		//$content[] = Format::date($posted);
		$content[] = $last_mess_write;
		if($mod_perm) {

			// $content[] = '<a href="index.php?modname=public_forum&amp;op=modthread&amp;idThread='.$idT.'&amp;search=1&amp;ini='.$ini_page.'" '
			// 	.'title="'.$lang->def('_MOD').' : '.strip_tags($title).'">'
			// 	.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.strip_tags($title).'" /></a>';
			// $content[] = '<a href="index.php?modname=public_forum&amp;op=delthread&amp;idThread='.$idT.'&amp;search=1&amp;ini='.$ini_page.'" '
			// 	.'title="'.$lang->def('_DEL').' : '.strip_tags($title).'">'
			// 	.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.strip_tags($title).'" /></a>';

			$content[] = '<div class="btn-group">
											<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=modthread&amp;idThread='.$idT.'&amp;search=1&amp;ini='.$ini_page.'" title="'.$lang->def('_MOD').' : '.strip_tags($title).'">
												<span class="glyphicon glyphicon-pencil"></span>
											</a>
											<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=delthread&amp;idThread='.$idT.'&amp;search=1&amp;ini='.$ini_page.'" title="'.$lang->def('_DEL').' : '.strip_tags($title).'">
												<span class="glyphicon glyphicon-remove"></span>
											</a>
										</div>';
		}
		$tb->addBody($content);
	}

	$text_inner = '<div class="col-xs-7 col-sm-6 col-md-4 col-lg-3 pull-right">'
								.Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search')
								.'<div class="input-group">'
								.Form::getSearchInputTextfield('', '', '', 'Cerca argomento...', '', '', '', '')
								.'<div class="input-group-btn"><button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-search"></span></button></div>'
								.'</div>'
								.Form::closeForm()
								.'</div>';
	
	$tb->addActionAdd($text_inner);

	$GLOBALS['page']->add($tb->getTable(), 'content');
	$GLOBALS['page']->add(
		$nav_bar->getNavBar($ini)
		.'</div>', 'content');
}

function forumsearchmessage() {
	$search_arg = $_SESSION['forum']['search_arg'];

	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('forum', 'lms');
	$id_thread = importVar('idThread', true, 0);
	$ini_thread = importVar('ini_thread');
	
	$query_id_forum = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = '".$id_thread."'";
	
	list($id_forum) = sql_fetch_row(sql_query($query_id_forum));
	
	if(!checkPublicForumPerm('view', $id_forum))
		die("You can't access");
	
	$sema_perm 	= checkPerm('sema', true);
	
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	$mod_perm	= checkPerm('mod', true);
	$acl_man 	=& Docebo::user()->getAclManager();

	$tb	= new Table(Get::sett('visuItem'), $lang->def('_CAPTION_FORUM_MESSAGE'), $lang->def('_CAPTION_FORUM_MESSAGE'));
	$tb->initNavBar('ini', 'link');
	$tb->setLink('index.php?modname=public_forum&amp;op=searchmessage&amp;idThread='.$id_thread.'&amp;ini_thread='.$ini_thread);
	$ini 	= $tb->getSelectedElement();
	$ini_page = $tb->getSelectedPage();

	// Some info about forum and thread
	$thread_query = "
	SELECT idForum, title, num_post, locked, erased
	FROM ".$GLOBALS['prefix_lms']."_forumthread
	WHERE idThread = '".$id_thread."'";
	list($id_forum, $thread_title, $tot_message, $locked_t, $erased_t) = sql_fetch_row(sql_query($thread_query));
	$forum_query = "
	SELECT title, locked
	FROM ".$GLOBALS['prefix_lms']."_forum
	WHERE idForum = '".$id_forum."'";
	list($forum_title, $locked_f) = sql_fetch_row(sql_query($forum_query));
	++$tot_message;

	//set as readed if needed
	if(isset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$id_thread])) unset($_SESSION['unreaded_forum'][PUBLIC_FORUM_COURSE_ID][$id_forum][$id_thread]);

	if( ($ini == 0) && (!isset($_GET['result'])) ) {
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_forumthread
		SET num_view = num_view + 1
		WHERE idThread = '".$id_thread."'");
	}
	$page_title = array(
		'index.php?modname=public_forum&amp;op=forum' => $lang->def('_FORUM'),
		'index.php?modname=public_forum&amp;op=search&amp;ini='.$ini_thread => $thread_title,
		$lang->def('_SEARCH_RESULT_FOR').' : '.$search_arg
	);
	if($erased_t && !$mod_perm && !$moderate) {

		$GLOBALS['page']->add(
			getTitleArea($page_title, 'forum')
			.'<div class="std_block">'
			.$lang->def('_CANNOTENTER')
			.'</div>', 'content');
		return;
	}
	// Who have semantic evaluation
	$re_sema = sql_query("
	SELECT DISTINCT idmsg
	FROM ".$GLOBALS['prefix_lms']."_forum_sema");
	while(list($msg_sema) = sql_fetch_row($re_sema)) $forum_sema[$msg_sema] = 1;

	// Find post
	$messages 		= array();
	$authors 		= array();
	$authors_names	= array();
	$authors_info	= array();
	$re_message = sql_query("
	SELECT idMessage, posted, title, textof, attach, locked, author, modified_by, modified_by_on
	FROM ".$GLOBALS['prefix_lms']."_forummessage
	WHERE idThread = '".$id_thread."'
	ORDER BY posted
	LIMIT $ini, ".Get::sett('visuItem'));
	while($record = mysql_fetch_assoc($re_message)) {

		$messages[$record['idMessage']] 	= $record;
		$authors[$record['author']] 		= $record['author'];
		if($record['modified_by'] != 0) {
			$authors[$record['modified_by']] 	= $record['modified_by'];
		}
	}
	$authors_names =& $acl_man->getUsers($authors);
	$level_name = CourseLevel::getLevels();

	// Retriving level and number of post of th authors
	$re_num_post = sql_query("
	SELECT u.idUser, u.level, COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_forummessage AS m, ".$GLOBALS['prefix_lms']."_courseuser AS u
	WHERE m.idCourse = '".(int)PUBLIC_FORUM_COURSE_ID."' AND m.author = u.idUser AND m.author IN ( ".implode($authors, ',')." )
	GROUP BY u.idUser, u.level");
	while( list($id_u, $level_u, $num_post_a) = sql_fetch_row($re_num_post) ) {

		$authors_info[$id_u] = array( 'num_post' => $num_post_a, 'level' => $level_name[$level_u] );
	}
	$type_h = array('forum_sender', 'forum_text');
	$cont_h = array($lang->def('_AUTHOR'), $lang->def('_TEXTOF'));
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	// Compose messagges display
	$path = $GLOBALS['where_files_relative'].'/appCore/'.Get::sett('pathphoto');
	while(list($id_message, $message_info) = each($messages)) {

		// sender info
		$m_author = $message_info['author'];

		if(isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_AVATAR] != '')
			$img_size = @getimagesize($path.$authors_names[$m_author][ACL_INFO_AVATAR]);

		// $sender = '<div class="forum_author">'
		// 	.( isset($authors_names[$m_author])
		// 		?( $authors_names[$m_author][ACL_INFO_LASTNAME].$authors_names[$m_author][ACL_INFO_FIRSTNAME] == '' ?
		// 			$acl_man->relativeId($authors_names[$m_author][ACL_INFO_USERID]) :
		// 			$authors_names[$m_author][ACL_INFO_LASTNAME].' '.$authors_names[$m_author][ACL_INFO_FIRSTNAME] )
		// 		: $lang->def('_UNKNOWN_AUTHOR')
		// 	)
		// 	.'</div>'
		// 	.'<div class="forum_level">'.$lang->def('_LEVEL').' : '.$authors_info[$m_author]['level'].'</div>'
		// 	.( isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_AVATAR] != ''
		// 		? '<img class="forum_avatar'.( $img_size[0] > 150 || $img_size[1] > 150 ? ' image_limit' : '' ).'" src="'.$path.$authors_names[$m_author][ACL_INFO_AVATAR].'" alt="'.$lang->def('_AVATAR').'" />'
		// 		: '')
		// 	.'<div class="forum_numpost">'.$lang->def('_NUMPOST').' : '
		// 	.( isset($authors_info[$m_author]['num_post'])
		// 		? $authors_info[$m_author]['num_post']
		// 		: 0 )
		// 	.'</div>'
		// 	.'<img src="'.getPathImage().'standard/identity.png" alt="&gt;" />&nbsp;'
		// 	.'<a href="index.php?modname=public_forum&amp;op=viewprofile&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'&amp;idThread='.$id_thread.'">'.$lang->def('_VIEW_PROFILE').'</a>';
		
		$sender = '<div class="forum_author">'
			.( isset($authors_names[$m_author])
				?( $authors_names[$m_author][ACL_INFO_LASTNAME].$authors_names[$m_author][ACL_INFO_FIRSTNAME] == '' ?
					$acl_man->relativeId($authors_names[$m_author][ACL_INFO_USERID]) :
					$authors_names[$m_author][ACL_INFO_LASTNAME].' '.$authors_names[$m_author][ACL_INFO_FIRSTNAME] )
				: $lang->def('_UNKNOWN_AUTHOR')
			)
			.'</div>'
			.(isset($authors_info[$m_author]) ? '<div class="forum_level">'.$authors_info[$m_author]['level'].'</div>' : '')
			.'<br/>'
			.'<div class="forum_numpost">'.$lang->def('_NUMPOST').' : '.(isset($authors_info[$m_author]['num_post']) ? $authors_info[$m_author]['num_post'] : 0 ).'</div>'
			.'<br />'
			.'<span class="glyphicon glyphicon-user"></span> &nbsp;'
			.'<a href="index.php?modname=public_forum&amp;op=viewprofile&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'&amp;idThread='.$id_thread.'">'
			.'<span>'.$lang->def('_VIEW_PROFILE').'</span>'
			.'</a>';

		// msg info
		$msgtext = '';

		$msgtext .= '<div class="forum_post_posted">'
			.$lang->def('_DATE').' : '.Format::date($message_info['posted'])
			.' ( '.loadDistance($message_info['posted']).' )'
			.'</div>';
		if($message_info['locked']) {

			$msgtext .= '<div class="forum_post_locked">'.$lang->def('_LOCKEDMESS').'</div>';
		} else {
			if($message_info['attach'] != '') {

				$msgtext .= '<div class="forum_post_attach">'
					.'<a href="index.php?modname=public_forum&amp;op=download&amp;id='.$id_message.'">'
					.$lang->def('_ATTACHMENT').' : '
					.'<img src="'.getPathImage('fw').mimeDetect($message_info['attach']).'" alt="'.$lang->def('_ATTACHMENT').'" /></a>'
					.'</div>';
			}

			$textof = str_replace('[quote]', '<blockquote class="forum_quote">', str_replace('[/quote]', '</blockquote>', $message_info['textof']));
			$msgtext .= '<div class="forum_post_title">'.$lang->def('_SUBJECT').' : '
						.( $search_arg !== ''
							? eregi_replace($search_arg, '<span class="filter_evidence">'.$search_arg.'</span>', $message_info['title'])
							: $message_info['title'] )
						.'</div>';
			$msgtext .= '<div class="forum_post_text">'
						.( $search_arg !== ''
							? eregi_replace($search_arg, '<span class="filter_evidence">'.$search_arg.'</span>', $textof)
							: $textof )
						.'</div>';

			if($message_info['modified_by'] != 0) {

				$modify_by = $message_info['modified_by'];
				$msgtext .= '<div class="forum_post_modified_by">'
						.$lang->def('_MODIFY_BY').' : '
						.( isset($authors_names[$modify_by])
							?( $authors_names[$modify_by][ACL_INFO_LASTNAME].$authors_names[$modify_by][ACL_INFO_FIRSTNAME] == '' ?
								$acl_man->relativeId($authors_names[$modify_by][ACL_INFO_USERID]) :
								$authors_names[$modify_by][ACL_INFO_LASTNAME].' '.$authors_names[$modify_by][ACL_INFO_FIRSTNAME] )
							: $lang->def('_UNKNOWN_AUTHOR')
						)
						.' '.$lang->def('_ON').' : '
						.Format::date($message_info['modified_by_on'])
						.'</div>';
			}

			if(isset($authors_names[$m_author]) && $authors_names[$m_author][ACL_INFO_SIGNATURE] != '') {
				$msgtext .= '<div class="forum_post_sign_separator"></div>'
					.'<div class="forum_post_sign">'
					.$authors_names[$m_author][ACL_INFO_SIGNATURE]
					.'</div>';
			}
		}
		$content = array($sender, $msgtext);
		$tb->addBody($content);

		// some action that you can do with this message
		//$action = '';
		// if($sema_perm) {
		// 	if(isset($forum_sema[$id_message])) $img_sema = 'sema_check';
		// 	else $img_sema = 'sema';
		// 	$action .= '<a href="index.php?modname=public_forum&amp;op=editsema&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
		// 			.'title="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'">'
		// 		.'<img src="'.getPathImage().'forum/'.$img_sema.'.gif" alt="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'" /> '
		// 		.$lang->def('_SEMATAG').'</a> ';
		// }
		// if($moderate || $mod_perm) {
		// 	if($message_info['locked']) {

		// 		$action .= '<a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
		// 				.'title="'.$lang->def('_DEMODERATE').' : '.strip_tags($message_info['title']).'">'
		// 			.'<img src="'.getPathImage().'standard/demoderate.png" alt="'.$lang->def('_DEMODERATE').' : '.strip_tags($message_info['title']).'" /> '
		// 			.$lang->def('_DEMODERATE').'</a> ';
		// 	} else {

		// 		$action .= '<a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
		// 				.'title="'.$lang->def('_MODERATE').' : '.strip_tags($message_info['title']).'">'
		// 			.'<img src="'.getPathImage().'standard/moderate.png" alt="'.$lang->def('_MODERATE').' : '.strip_tags($message_info['title']).'" /> '
		// 			.$lang->def('_MODERATE').'</a> ';
		// 	}
		// }
		// if((!$locked_t && !$locked_f) || $mod_perm || $moderate) {
		// 	$action .= '<a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
		// 			.'title="'.$lang->def('_REPLY').' : '.strip_tags($message_info['title']).'">'
		// 		.'<img src="'.getPathImage().'standard/reply.png" alt="'.$lang->def('_REPLY').' : '.strip_tags($message_info['title']).'" /> '
		// 		.$lang->def('_QUOTE').'</a>';
		// }
		// if($moderate || $mod_perm || ($m_author == getLogUserId()) ) {

		// 	$action .= '<a href="index.php?modname=public_forum&amp;op=modmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
		// 			.'title="'.$lang->def('_MOD_MESSAGE').' : '.strip_tags($message_info['title']).'">'
		// 		.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'" /> '
		// 		.$lang->def('_MOD').'</a>'
		// 		.'<a href="index.php?modname=public_forum&amp;op=delmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
		// 			.'title="'.$lang->def('_DEL').' : '.strip_tags($message_info['title']).'">'
		// 		.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.strip_tags($message_info['title']).'" /> '
		// 		.$lang->def('_DEL').'</a> ';
		// }
		
		$action = '<div class="btn-group hidden-xs pull-right">';

		if($sema_perm) {
			if(isset($forum_sema[$id_message])) $img_sema = 'sema_check';
			else $img_sema = 'sema';
			$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=editsema&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<img src="'.getPathImage().'forum/'.$img_sema.'.gif" alt="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'" /> '.$lang->def('_SEMATAG')
									.'</a>';
			// $action .= '<a href="index.php?modname=public_forum&amp;op=editsema&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
			// 		.'title="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'">'
			// 	.'<img src="'.getPathImage().'forum/'.$img_sema.'.gif" alt="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'" /> '
			// 	.$lang->def('_SEMATAG').'</a> ';
		}
		if($moderate || $mod_perm) {
			if($message_info['locked']) {
				$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'>
											<span class="glyphicon glyphicon-ban-circle"></span> &nbsp;
											<span>'.$lang->def('_DEMODERATE').'</span>
										</a>';
				// $action .= '<li><a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
				// 		.'title="'.$lang->def('_DEMODERATE').' : '.strip_tags($message_info['title']).'">'
				// 	.'<img src="'.getPathImage().'standard/demoderate.png" alt="'.$lang->def('_DEMODERATE').' : '.strip_tags($message_info['title']).'" /> '
				// 	.$lang->def('_DEMODERATE').'</a></li>';
			} else {
				$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-ban-circle"></span> &nbsp;
											<span>'.$lang->def('_MODERATE').'</span>
										</a>';
				// $action .= '<li><a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
				// 		.'title="'.$lang->def('_MODERATE').' : '.strip_tags($message_info['title']).'">'
				// 	.'<img src="'.getPathImage().'standard/moderate.png" alt="'.$lang->def('_MODERATE').' : '.strip_tags($message_info['title']).'" /> '
				// 	.$lang->def('_MODERATE').'</a></li>';
			}
		}
		if((!$locked_t && !$locked_f) || $mod_perm || $moderate) {
			$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-comment"></span> &nbsp;
										<span>'.$lang->def('_QUOTE').'</span>
									</a>';
			// $action .= '<a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" title="'.$lang->def('_REPLY').' : '.strip_tags($message_info['title']).'">
			// 							<img src="'.getPathImage().'standard/reply.png" alt="'.$lang->def('_REPLY').' : '.strip_tags($message_info['title']).'" /> '.$lang->def('_QUOTE')
			// 						.'</a>';
		}
		if($moderate || $mod_perm || ($m_author == getLogUserId()) ) {

			$action .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=modmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-pencil"></span> &nbsp;
										<span>'.$lang->def('_MOD_MESSAGE').'</span>
									</a>
									<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=delmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-remove"></span> &nbsp;
										<span>'.$lang->def('_DEL').'</span>
									</a>';

			// $action .= '<li><a href="index.php?modname=public_forum&amp;op=modmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
			// 		.'title="'.$lang->def('_MOD_MESSAGE').' : '.strip_tags($message_info['title']).'">'
			// 	.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'" /> '
			// 	.$lang->def('_MOD').'</a></li>'
			// 	.'<li><a href="index.php?modname=public_forum&amp;op=delmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'" '
			// 		.'title="'.$lang->def('_DEL').' : '.strip_tags($message_info['title']).'">'
			// 	.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.strip_tags($message_info['title']).'" /> '
			// 	.$lang->def('_DEL').'</a></li>';
		}
		$action .= '</div>';
		$action .= '<div class="dropdown visible-xs pull-right">
								<button class="btn btn-default dropdown-toggle" type="button" id="messageActions-'.$id_message.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								  <span class="glyphicon glyphicon-cog"></span> &nbsp;
								  Azioni &nbsp;
								  <span class="caret"></span>
								</button>
								<ul class="dropdown-menu" aria-labelledby="messageActions-'.$id_message.'">';
		if($sema_perm) {
			if(isset($forum_sema[$id_message])) $img_sema = 'sema_check';
			else $img_sema = 'sema';
			$action .= '<li><a href="index.php?modname=public_forum&amp;op=editsema&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<img src="'.getPathImage().'forum/'.$img_sema.'.gif" alt="'.$lang->def('_MOD').' : '.strip_tags($message_info['title']).'" /> '.$lang->def('_SEMATAG')
								.'</a></li>';
		}
		if($moderate || $mod_perm) {
			if($message_info['locked']) {
				$action .= '<li><a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-ban-circle"></span> &nbsp;
											<span>'.$lang->def('_MODERATE').'</span>
										</a></li>';
			} else {
				$action .= '<li><a href="index.php?modname=public_forum&amp;op=moderatemessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-ban-circle"></span> &nbsp;
											<span>'.$lang->def('_DEMODERATE').'</span>
										</a></li>';
			}
		}
		if((!$locked_t && !$locked_f) || $mod_perm || $moderate) {
			$action .= '<li><a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-comment"></span> &nbsp;
										<span>'.$lang->def('_QUOTE').'</span>
									</a></li>';
		}
		if(!$locked_t && !$locked_f && !$message_info['locked'] && $write_perm) {
			$action .= '<li><a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-comment"></span> &nbsp;
										<span>'.$lang->def('_QUOTE').'</span>
									</a></li>';
		}
		if($moderate || $mod_perm || ($m_author == getLogUserId()) ) {
			$action .= '<li><a href="index.php?modname=public_forum&amp;op=modmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-pencil"></span> &nbsp;
										<span>'.$lang->def('_MOD_MESSAGE').'</span>
									</a></li>
									<li><a href="index.php?modname=public_forum&amp;op=delmessage&amp;idMessage='.$id_message.'&amp;ini='.$ini_page.'">
										<span class="glyphicon glyphicon-remove"></span> &nbsp;
										<span>'.$lang->def('_DEL').'</span>
									</a></li>';
		}

		$action .= '</ul></div>';

		// $tb->addBodyExpanded($action, 'forum_action');
		$tb->addBodyExpanded($action);
	}

	// if( (!$locked_t && !$locked_f) || $mod_perm || $moderate ) {

	// 	$tb->addActionAdd(
	// 		'<a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'" title="'.$lang->def('_REPLY').'">'
	// 		.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def('_ADD').'" /> '
	// 		.$lang->def('_REPLY').'</a>'
	// 	);
	// }


	$GLOBALS['page']->add(getTitleArea($page_title, 'forum'), 'content');
		// .'<div class="std_block">'
		// .Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search&amp;idThread='.$id_thread)
		// .'<div class="quick_search_form">'
		// .'<label for="search_arg">'.$lang->def('_SEARCH_LABEL').'</label> '
		// .Form::getInputTextfield(	'search_t',
		// 							'search_arg',
		// 							'search_arg',
		// 							'',
		// 							$lang->def('_SEARCH'), 255, '' )
		// .'<input class="search_b" type="submit" id="search_button" name="search_button" value="'.$lang->def('_SEARCH').'" />'
		// .'</div>'
		// .Form::closeForm(), 'content');

	$text_inner = '<div class="btn-group col-lg-9 visible-lg">';
	if( (!$locked_t && !$locked_f) || $mod_perm || $moderate ) {
		$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-plus-sign"></span> &nbsp;
											<span>'.$lang->def('_REPLY').'</span>
										</a>';
	}
	if($moderate || $mod_perm) {
		$text_inner .= '<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=modstatusthread&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-'.($locked_t ? 'folder-open' : 'lock').'"></span> &nbsp;
											<span>'.($locked_t ? $lang->def('_FREETHREAD') : $lang->def('_LOCKTHREAD')).'</span>
										</a>
										<a class="btn btn-default" href="index.php?modname=public_forum&amp;op=changeerased&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-'.($erased_t ? 'folder-open' : 'ban-circle').'"></span> &nbsp;
											<span>'.($erased_t ? $lang->def('_UNERASE') : $lang->def('_MODERATE')).'</span>
										</a>';
	}
	$text_inner .= '</div>';
	$text_inner .= '<div class="dropdown col-xs-5 col-sm-4 hidden-lg pull-left">
										<button class="btn btn-default dropdown-toggle" type="button" id="tableActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										  <span class="glyphicon glyphicon-cog"></span> &nbsp;
										  Operazioni &nbsp;
										  <span class="caret"></span>
										</button>
										<ul class="dropdown-menu dropdown-menu-left" aria-labelledby="tableActions">';
	if( (!$locked_t && !$locked_f) || $mod_perm || $moderate ) {
		$text_inner .= '<li><a href="index.php?modname=public_forum&amp;op=addmessage&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-plus-sign"></span> &nbsp;
											<span>'.$lang->def('_REPLY').'</span>
										</a></li>';
	}
	if($moderate || $mod_perm) {
		$text_inner .= '<li><a href="index.php?modname=public_forum&amp;op=modstatusthread&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-'.($locked_t ? 'folder-open' : 'lock').'"></span> &nbsp;
											<span>'.($locked_t ? $lang->def('_FREETHREAD') : $lang->def('_LOCKTHREAD')).'</span>
										</a></li>
										<li><a href="index.php?modname=public_forum&amp;op=changeerased&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">
											<span class="glyphicon glyphicon-'.($erased_t ? 'folder-open' : 'ban-circle').'"></span> &nbsp;
											<span>'.($erased_t ? $lang->def('_UNERASE') : $lang->def('_MODERATE')).'</span>
										</a></li>';
	}
	$text_inner .= '</ul>
								</div>
								<div class="col-xs-7 col-sm-6 col-md-4 col-lg-3 pull-right">'
								.Form::openForm('search_forum', 'index.php?modname=public_forum&amp;op=search')
								.'<div class="input-group">'
								.Form::getSearchInputTextfield('', '', '', 'Cerca argomento...', '', '', '', '')
								.'<div class="input-group-btn"><button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-search"></span></button></div>'
								.'</div>'
								.Form::closeForm()
								.'</div>';

	if($text_inner != '') $tb->addActionAdd($text_inner);

	// if($moderate || $mod_perm) {
	// 	$GLOBALS['page']->add(
	// 		'<div class="forum_action_top">'
	// 		.'<a href="index.php?modname=public_forum&amp;op=modstatusthread&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">'
	// 		.( $locked_t
	// 			?'<img src="'.getPathImage().'standard/msg_read.png" alt="'.$lang->def('_FREE').'" /> '.$lang->def('_FREETHREAD')
	// 			: '<img src="'.getPathImage().'standard/locked.png" alt="'.$lang->def('_LOCKTHREAD').'" /> '.$lang->def('_LOCKTHREAD') )
	// 		.'</a> '
	// 		.'<a href="index.php?modname=public_forum&amp;op=changeerased&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">'
	// 		.( $erased_t
	// 			? '<img src="'.getPathImage().'standard/msg_read.png" alt="'.$lang->def('_UNERASE').'" /> '.$lang->def('_UNERASE')
	// 			: '<img src="'.getPathImage().'standard/moderate.png" alt="'.$lang->def('_MODERATE').'" /> '.$lang->def('_MODERATE')
	// 		)
	// 		.'</a>'
	// 		.'</div>' , 'content');
	// }

	$GLOBALS['page']->add($tb->getTable(), 'content');
	// if($moderate || $mod_perm) {
	// 	$GLOBALS['page']->add(
	// 		'<div class="forum_action_bottom">'
	// 		.'<a href="index.php?modname=public_forum&amp;op=modstatusthread&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">'
	// 		.( $locked_t
	// 			?'<img src="'.getPathImage().'standard/msg_read.png" alt="'.$lang->def('_FREE').'" /> '.$lang->def('_FREETHREAD')
	// 			: '<img src="'.getPathImage().'standard/locked.png" alt="'.$lang->def('_LOCKTHREAD').'" /> '.$lang->def('_LOCKTHREAD') )
	// 		.'</a> '
	// 		.'<a href="index.php?modname=public_forum&amp;op=changeerased&amp;idThread='.$id_thread.'&amp;ini='.$ini_page.'">'
	// 		.( $erased_t
	// 			? '<img src="'.getPathImage().'standard/msg_read.png" alt="'.$lang->def('_UNERASE').'" /> '.$lang->def('_UNERASE')
	// 			: '<img src="'.getPathImage().'standard/moderate.png" alt="'.$lang->def('_MODERATE').'" /> '.$lang->def('_MODERATE')
	// 		)
	// 		.'</a>'
	// 		.'</div>' , 'content');
	// }

	$GLOBALS['page']->add($tb->getNavBar($ini, $tot_message).'</div>', 'content');

}

//-XXX: notify functions-----------------------------------------------------//

/**
 * Register a new notify
 * @param string	$notify_is_a 	specifie if the notify is for a thread or for a forum
 * @param int		$id_notify 		specifie the id of the resource
 * @param int		$id_user 		the user
 *
 * @return	bool	true if success false otherwise
 */
function setNotify($notify_is_a, $id_notify, $id_user) {
	$query_notify = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_forum_notifier
	( id_notify, id_user, notify_is_a ) VALUES (
		'".$id_notify."',
		'".$id_user."',
		'".( $notify_is_a == 'forum' ? 'forum' : 'thread' )."' )";
	return sql_query($query_notify);
}

/**
 * Erase a register notify
 * @param string	$notify_is_a 	specifie if the notify is for a thread or for a forum
 * @param int		$id_notify 		specifie the id of the resource
 * @param int		$id_user 		the user
 *
 * @return	bool	true if success false otherwise
 */
function unsetNotify($notify_is_a, $id_notify, $id_user = false) {
	$query_notify = "
	DELETE FROM ".$GLOBALS['prefix_lms']."_forum_notifier
	WHERE id_notify = '".$id_notify."' AND
		notify_is_a = '".( $notify_is_a == 'forum' ? 'forum' : 'thread' )."' ";
	if($id_user !== false)  $query_notify .= " AND id_user = '".$id_user."'";
	return sql_query($query_notify);
}

/**
 * Return if a user as set a notify for a resource
 * @param string	$notify_is_a 	specifie if the notify is for a thread or for a forum
 * @param int		$id_notify 		specifie the id of the resource
 * @param int		$id_user 		the user
 *
 * @return	bool	true if exists false otherwise
 */
function issetNotify($notify_is_a, $id_notify, $id_user) {
	$query_notify = "
	SELECT id_notify
	FROM ".$GLOBALS['prefix_lms']."_forum_notifier
	WHERE id_notify = '".$id_notify."' AND
		id_user = '".$id_user."' AND
		notify_is_a = '".( $notify_is_a == 'forum' ? 'forum' : 'thread' )."'";
	$re = sql_query($query_notify);
	return ( mysql_num_rows($re) == 0 ? false : true );
}

/**
 * Return all the users registered notify
 * @param int		$id_user 		the user
 * @param string	$notify_is_a 	specifie if the notify is for a thread or for a forum
 *
 * @return	array	[thread]=>(  [id] => id, ...), [forum]=>(  [id] => id, ...)
 */
function getAllNotify($id_user, $notify_is_a = false) {
	$notify = array();
	$query_notify = "
	SELECT id_notify, notify_is_a
	FROM ".$GLOBALS['prefix_lms']."_forum_notifier
	WHERE id_user = '".$id_user."'";
	if($notify_is_a !== false) $query_notify .= " AND notify_is_a = '".( $notify_is_a == 'forum' ? 'forum' : 'thread' )."'";
	$re = sql_query($query_notify);
	while(list($id_n, $n_is_a) = sql_fetch_row($re)) {

		$notify[$n_is_a][$id_n] = $id_n;
	}
	return $notify;
}

function launchNotify($notify_is_a, $id_notify, $description, &$msg_composer) {

	require_once(_base_.'/lib/lib.eventmanager.php');

	$recipients = array();
	$query_notify = "
	SELECT id_user
	FROM ".$GLOBALS['prefix_lms']."_forum_notifier
	WHERE id_notify = '".$id_notify."' AND
		notify_is_a = '".( $notify_is_a == 'forum' ? 'forum' : 'thread' )."' AND
		id_user <> '".getLogUserId()."'";
	if($notify_is_a !== false) $query_notify .= " AND notify_is_a = '".( $notify_is_a == 'forum' ? 'forum' : 'thread' )."'";
	$re = sql_query($query_notify);
	echo $query_notify;
	while(list($id_user) = sql_fetch_row($re)) {

		$recipients[] = $id_user;
	}
	if(!empty($recipients)) {

		createNewAlert(		( $notify_is_a == 'forum' ? 'ForumNewThread' : 'ForumNewResponse' ),
							'forum',
							( $notify_is_a == 'forum' ? 'new_thread' : 'responce' ),
							1,
							$description,
							$recipients,
							$msg_composer );
	}
	return;
}

function moveThread($id_thread, $id_forum)
{
	require_once(_base_.'/lib/lib.form.php');
	
	$lang =& DoceboLanguage::CreateInstance('forum');
	
	$mod_perm 	= checkPerm('mod', true);
	$moderate 	= checkPublicForumPerm('moderate', $id_forum);
	
	$action = importVar('action', true, 0);
	
	if(isset($_GET['confirm']))
	{
		$id_new_forum = importVar('new_forum', true, 0);
		$id_thread = importVar('id_thread', true, 0);
		$id_forum = importVar('id_forum', true, 0);
		$confirm = importVar('confirm', true, 0);
		
		if ($confirm)
		{
			// Move the thread to the new forum
			$query = "UPDATE ".$GLOBALS['prefix_lms']."_forumthread" .
					" SET idForum = '".$id_new_forum."'" .
					" WHERE idThread = '".$id_thread."'";
			
			$result = sql_query($query);
			
			// Select thenumber of the post in the thread
			$query_2 = "SELECT num_post" .
						" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
						" WHERE idThread = '".$id_thread."'";
			
			list($num_post) = sql_fetch_row(sql_query($query_2));
			
			// Update the forum info
			$query_3 = "SELECT idForum, num_thread, num_post" .
						" FROM ".$GLOBALS['prefix_lms']."_forum" .
						" WHERE idForum = '".$id_forum."'" .
						" OR idForum = '".$id_new_forum."'";
			
			$result_3 = sql_query($query_3);
			
			$num_post_update = array();
			$num_thread_update = array();
			
			while(list($idForum, $num_thread_3, $num_post_3) = sql_fetch_row($result_3))
			{
				if ($idForum == $id_forum)
				{
					$num_post_update[$idForum] = $num_post_3 - $num_post;
					$num_thread_update[$idForum] = $num_thread_3 - 1;
				}
				else
				{
					$num_post_update[$idForum] = $num_post_3 + $num_post;
					$num_thread_update[$idForum] = $num_thread_3 + 1;
				}
			}
			
			$last_message_update = array();
			
			$query_4 = "SELECT idMessage" .
						" FROM ".$GLOBALS['prefix_lms']."_forummessage" .
						" WHERE idThread IN" .
						"(" .
							" SELECT idThread" .
							" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
							" WHERE idForum = '".$id_forum."'" .
						")" .
						" ORDER BY posted DESC" .
						" LIMIT 0,1";
			
			list($last_message_update[$id_forum]) = sql_fetch_row(sql_query($query_4));
			
			$query_5 = "SELECT idMessage" .
						" FROM ".$GLOBALS['prefix_lms']."_forummessage" .
						" WHERE idThread IN" .
						"(" .
							" SELECT idThread" .
							" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
							" WHERE idForum = '".$id_new_forum."'" .
						")" .
						" ORDER BY posted DESC" .
						" LIMIT 0,1";
			
			list($last_message_update[$id_new_forum]) = sql_fetch_row(sql_query($query_5));
			
			$query_update_1 = "UPDATE ".$GLOBALS['prefix_lms']."_forum" .
						" SET num_post = '".$num_post_update[$id_forum]."'," .
								" num_thread='".$num_thread_update[$id_forum]."'," .
								" last_post = '".$last_message_update[$id_forum]."'" .
						" WHERE idForum = '".$id_forum."'";
			
			$result_update_1 = sql_query($query_update_1);
			
			$query_update_2 = "UPDATE ".$GLOBALS['prefix_lms']."_forum" .
						" SET num_post = '".$num_post_update[$id_new_forum]."'," .
								" num_thread='".$num_thread_update[$id_new_forum]."'," .
								" last_post = '".$last_message_update[$id_new_forum]."'" .
						" WHERE idForum = '".$id_new_forum."'";
			
			$result_update_2 = sql_query($query_update_2);
		}
		Util::jump_to('index.php?modname=public_forum&amp;op=thread&idForum='.$id_forum);
	}
	
	if ($action)
	{
		$id_new_forum = importVar('new_forum', true, 0);
		$id_thread = importVar('id_thread', true, 0);
		$id_forum = importVar('id_forum', true, 0);
		
		list($title) = sql_fetch_row(sql_query("SELECT title" .
													" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
													" WHERE idThread = '".$id_thread."'"));
		
		list($from_forum) = sql_fetch_row(sql_query("SELECT title" .
													" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
													" WHERE idForum = '".$id_forum."'"));
		
		list($to_forum) = sql_fetch_row(sql_query("SELECT title" .
													" FROM ".$GLOBALS['prefix_lms']."_forum" .
													" WHERE idForum = '".$id_new_forum."'"));
		
		$GLOBALS['page']->add
		(
			getTitleArea($lang->def('_MOVE_TITLE'), 'forum')
			.'<div class="std_block">'
			.getModifyUi(	$lang->def('_AREYOUSURE_MOVE'),
							'<span>'.$lang->def('_TITLE').' : </span> "'.$title.'"'.' '.$lang->def('_FROM_FORUM').' "'.$from_forum.'" '.$lang->def('_TO_FORUM').' "'.$to_forum.'"',
							true,
							'index.php?modname=public_forum&amp;op=movethread&amp;new_forum='.$id_new_forum.'&amp;id_thread='.$id_thread.'&amp;id_forum='.$id_forum.'&amp;confirm=1',
							'index.php?modname=public_forum&amp;op=movethread&amp;id_forum='.$id_forum.'&amp;confirm=0'
						)
			.'</div>', 'content'
		);
	}
	else
	{
		$id_course = (int)PUBLIC_FORUM_COURSE_ID;
		$id_forum = importVar('id_forum', true, 0);
		
		$list_forum = array();
		
		$query = "SELECT idForum, title" .
				" FROM ".$GLOBALS['prefix_lms']."_forum" .
				" WHERE idCourse = '".$id_course."'" .
				" AND idForum <> '".$id_forum."'";
		
		$result = sql_query($query);
		
		while (list($id_forum_b, $title) = sql_fetch_row($result))
			$list_forum[$id_forum_b] = $title;
		
		$GLOBALS['page']->add
		(
			getTitleArea($lang->def('_MOVE_TITLE'), 'forum')
			.'<div class="std_block">'
			.Form::openForm('move_forum', 'index.php?modname=public_forum&amp;op=movethread&amp;id_thread='.$id_thread.'&amp;id_forum='.$id_forum.'&amp;action=1')
			.'<div class="form_line_l">'
			.Form::getDropdown($lang->def('_MOVE_TO_FORUM'), 'new_forum', 'new_forum', $list_forum)
			.' <input class="button_nowh" type="submit" id="move_thread" name="move_thread" value="'.$lang->def('_MOVE').'" />'
			.'</div>'
			.Form::closeForm()
			.'</div>'
			, 'content'
		);
	}
}

function export() {

	require_once(_base_.'/lib/lib.download.php' );
	require_once(_adm_.'/lib/lib.tags.php');
	
	$acl_man =& Docebo::user()->getAclManager();
	$tags = new Tags('lms_forum');
	$id_forum = Get::req('idForum', DOTY_INT, 0);
	$csv_string = '';
	$file_nme = '';
	$tag_list = array();
	
	if($id_forum)
	{
		$query =	"SELECT idThread, title, num_post"
					." FROM ".$GLOBALS['prefix_lms']."_forumthread"
					." WHERE idForum = '".$id_forum."'";
		
		$result = sql_query($query);
		
		if(mysql_num_rows($result));
		{
			$tmp = array();
			$id_list = array();
			
			while(list($id_thread, $thread_title, $num_post) = sql_fetch_row($result))
			{
				$tmp['int'] = '"nomethread";"n.msg";"titolomsg";"autore";"data";"corpomsg";"allegato";"id_msg"';
				
				$query_msg = "SELECT title, author, posted, textof, attach, idMessage"
							." FROM ".$GLOBALS['prefix_lms']."_forummessage"
							." WHERE idThread = '".$id_thread."'";
				
				$result_msg = sql_query($query_msg);
				
				$num_post++;
				
				while(list($message_title, $author, $posted, $textof, $attach, $idMessage) = sql_fetch_row($result_msg))
				{
					$sender_info = $acl_man->getUser($author, false);
					$author = ( $sender_info[ACL_INFO_LASTNAME].$sender_info[ACL_INFO_FIRSTNAME] == '' ?
								$acl_man->relativeId($sender_info[ACL_INFO_USERID]) :
								$sender_info[ACL_INFO_LASTNAME].' '.$sender_info[ACL_INFO_FIRSTNAME] );
					
					$posted = Format::date($posted);
					
					$tmp[$idMessage] = '"'.str_replace('"', '\"', $thread_title).'";"'.$num_post.'";"'.str_replace('"', '\"', $message_title).'";"'.$author.'";"'.$posted.'";"'.str_replace('"', '\"', $textof).'";"'.$attach.'";"'.$idMessage.'"';
					$id_list[] = $idMessage;
				}
			}
			
			$tags_associated = $tags->getResourcesOccurrenceTags($id_list);
			
			$number_of_tag = 0;
			
			if(count($tags_associated))
			{
				foreach($tags_associated as $tag_tmp)
					foreach($tag_tmp as $tmp_tag)
						if(!in_array($tmp_tag['tag'], $tag_list))
						{
							$tag_list[] = $tmp_tag['tag'];
							$number_of_tag++;
						}
				
				reset($tags_associated);
				
				foreach($tag_list as $tag_name)
					$tmp['int'] .= ';"'.str_replace('"', '\"', $tag_name).'"';
				
				reset($tag_list);
			}
			
			$csv_string .= $tmp['int']."\r\n";
			
			unset($tmp['int']);
			
			foreach($tmp as $id_message => $string)
			{
				$csv_string .= $string;
				
				if(count($tags_associated))
				{
					if(isset($tags_associated[$id_message]))
					{
						foreach($tag_list as $tag_name)
						if(isset($tags_associated[$id_message][$tag_name]))
								$csv_string .= ';"'.$tags_associated[$id_message][$tag_name]['occurences'].'"';
							else
								$csv_string .= ';"0"';
					}
					else
						for($i = 0; $i < $number_of_tag; $i++)
							$csv_string .= ';"0"';
				}
				
				$csv_string .= "\r\n";
			}
			
			$query_forum =	"SELECT title"
							." FROM ".$GLOBALS['prefix_lms']."_forum"
							." WHERE idForum = '".$id_forum."'";
			
			list($forum_title) = sql_fetch_row(sql_query($query_forum));
			
			$file_name = str_replace(
			array('\\', '/', 	':', 	'\'', 	'\*', 	'?', 	'"', 	'<', 	'>', 	'|'),
			array('', 	'', 	'', 	'', 	'', 	'', 	'', 	'', 	'', 	'' ),
			$forum_title).'.csv';
			
			sendStrAsFile($csv_string, $file_name);
		}
	}
}


function getEmoticonsArr() {
	return array(
		'access',
		'agent',
		'amor',
		'angel_smile',
		'angry_smile',
		'arts',
		'atlantik',
		'background',
		'bell',
		'blank',
		'broken_heart',
		'browser',
		'bug',
		'cache',
		'cake',
		'clanbomber',
		'colors',
		'confused_smile',
		'cookie',
		'cry_smile',
		'date',
		'designer',
		'devil_smile',
		'edu_languages',
		'edu_mathematics',
		'edu_miscellaneous',
		'edu_science',
		'email',
		'embaressed_smile',
		'envelope',
		'error',
		'filetypes',
		'heart',
		'important',
		'irkick',
		'kalarm',
		'kalzium',
		'kasteroids',
		'kate',
		'kbrunch',
		'kcalc',
		'kchart',
		'kcoloredit',
		'kdict',
		'kdmconfig',
		'kfm_home',
		'kiss',
		'kjobviewer',
		'knewsticker',
		'knotes',
		'kopete',
		'kopete_offline',
		'kopete_some_online',
		'korganizer',
		'korn',
		'ktip',
		'kweather',
		'laptop_pcmcia',
		'licq',
		'lightbulb',
		'locale',
		'mycomputer',
		'omg_smile',
		'package_favorite',
		'package_games_strategy',
		'package_toys',
		'personal',
		'regular_smile',
		'sad_smile',
		'shades_smile',
		'teeth_smile',
		'thumbs_down',
		'thumbs_up',
		'tounge_smile',
		'whatchutalkingabout_smile',
		'wink_smile',
	);
}


//---------------------------------------------------------------------------//

function checkPublicForumPerm($role, $id_forum) {
	if ($role == 'view') return true;
	if(checkPerm('mod', true)) return true;
	else return false;
}

function checkPublicForumPerm_old($role, $id_forum)
{
	if(checkPerm('mod', true)) return true;

	$res = false;
	$role_id = "";
	$user =& $GLOBALS['current_user'];
	$acl = new DoceboACL();
	
	$role_id = '/lms/course/public/public_forum/'.$id_forum.'/'.$role;
	
	if (($role_id !== "") && ($acl->getRoleST($role_id) != false))
		$res = $user->matchUserRole($role_id);
	
	return $res;
}

function forumDispatch($op) {

	require_once(_base_.'/lib/lib.urlmanager.php');
	$url_man =& UrlManager::getInstance('forum');
	$url_man->setStdQuery('index.php?modname=public_forum&op=forum');

	switch($op) {
		case "public_forum" :
		case "forum" : {
			forum();
		};break;
		//-----------------------------------------------//
		case "addforum" : {
			addforum();
		};break;
		case "insforum" : {
			insforum();
		};break;
		//-----------------------------------------------//
		case "modforum" : {
			modforum();
		};break;
		case "upforum" : {
			upforum();
		};break;
		case "downforum" : {
			moveforum( $_GET['idForum'], 'down');
		};break;
		case "moveupforum" : {
			moveforum( $_GET['idForum'], 'up');
		};break;
		case "modstatus" : {
			changestatus();
		};break;
		case "export":
			export();
		break;
		//-----------------------------------------------//
		case "delforum" : {
			delforum();
		};break;
		//-----------------------------------------------//
		case "modforumaccess": {
			modforumaccess();
		};break;
		//-----------------------------------------------//
		case "thread" : {
			thread();
		};break;
		//-----------------------------------------------//
		case "addthread" : {
			addthread();
		};break;
		case "insthread" : {
			insthread();
		};break;
		//-----------------------------------------------//
		case "modthread" : {
			modthread();
		};break;
		case "movethread":
		{
			$id_thread = importVar('id_thread', true, 0);
			$id_forum = importVar('id_forum', true, 0);
			moveThread($id_thread, $id_forum);
		}
		break;
		case "upthread" : {
			upthread();
		};break;
		//-----------------------------------------------//
		case "delthread" : {
			delthread();
		};break;
		//-----------------------------------------------//
		case "message" : {
			message();
		};break;
		case "moderatemessage" : {
			moderatemessage();
		};break;
		case "modstatusthread" : {
			modstatusthread();
		};break;
		case "changeerased" : {
			changeerase();
		};break;
		//-----------------------------------------------//
		case "addmessage" : {
			addmessage();
		};break;
		case "insmessage" : {
			insmessage();
		};break;
		//-----------------------------------------------//
		case "modmessage" : {
			modmessage();
		};break;
		case "upmessage" : {
			upmessage();
		};break;
		//-----------------------------------------------//
		case "delmessage" : {
			delmessage();
		};break;
		//-----------------------------------------------//
		case "viewprofile" : {
			viewprofile();
		};break;
		//-----------------------------------------------//
		case "editsema" : {
			editsema();
		};break;
		case "savesema" : {
			savesema();
		};break;
		//-----------------------------------------------//
		case "download" : {
			$query = "SELECT idForum" .
					" FROM ".$GLOBALS['prefix_lms']."_forumthread" .
					" WHERE idThread = " .
					"(" .
						" SELECT idThread" .
						" FROM ".$GLOBALS['prefix_lms']."_forummessage" .
						" WHERE idMessage = '".(int)$_GET['id']."'" .
					")";
			
			list($id_forum) = sql_fetch_row(sql_query($query));
			
			checkPublicForumPerm('view', $id_forum);
			
			require_once(_base_.'/lib/lib.download.php' );

			//find file
			list($title, $attach) = sql_fetch_row(sql_query("
			SELECT title, attach
			FROM ".$GLOBALS['prefix_lms']."_forummessage
			WHERE idMessage='".(int)$_GET['id']."'"));
			if(!$attach) {
				$GLOBALS['page']->add( getErrorUi('Sorry, such file does not exist!'), 'content');
				return;
			}
			//recognize mime type
			$expFileName = explode('.', $attach);
			$totPart = count($expFileName) - 1;

			$path = '/appLms/'.Get::sett('pathforum');
			//send file
			sendFile($path, $attach, $expFileName[$totPart]);
		};break;
		//-----------------------------------------------//
		case "search" : {
			forumsearch();
		};break;
		case "searchmessage" : {
			forumsearchmessage();
		};break;
		//-----------------------------------------------//
	}
}

?>
