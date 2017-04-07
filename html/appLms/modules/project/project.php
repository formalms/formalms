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
 * @version 	$Id: project.php 1002 2007-03-24 11:55:51Z fabio $
 */

if((Docebo::user()->isAnonymous()) || (!checkPerm('view', true))) die("You can't access!");


define("_FPATH_INTERNAL", "/appLms/".Get::sett('pathprj'));
define("_FPATH", $GLOBALS["where_files_relative"]._FPATH_INTERNAL);

require_once($GLOBALS["where_lms"].'/lib/lib.stats.php');

function project() {
	checkPerm('view');

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	require_once( _base_.'/lib/lib.table.php' );

	$myprj = userProjectsList(Docebo::user()->getIdSt());

	$mod_perm = checkPerm('mod', true);
	$del_perm = checkPerm('del', true);

	//area title
	$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project")
		.'<div class="std_block">'."\n");

	$tab = new Table(0, $lang->def("_MYPRJ"), $lang->def("_PROJECT_SUMMARY"));

	$content_h = array( $lang->def("_TITLE"), $lang->def("_PROGRESS"), $lang->def("_PERCENTAGE") );
	$type_h = array( '', '', 'image' );
	if($mod_perm) {
		$content_h[] = '<img src="'.getPathImage().'standard/moduser.png" '.
			'alt="'.$lang->def("_MODPROJECTADMIN").'" title="'.$lang->def("_MODPROJECTADMIN").'" />';
		$type_h[] = 'image' ;
		$content_h[] = '<img src="'.getPathImage().'standard/edit.png" '.
			'alt="'.$lang->def("_MOD").'" title="'.$lang->def("_MOD").'" />';
		$type_h[] = 'image' ;
	}
	if($del_perm) {
		$content_h[] = '<img src="'.getPathImage().'standard/delete.png" '.
			'alt="'.$lang->def("_DEL").'" title="'.$lang->def("_DEL").'" />';
		$type_h[] = 'image';
	}


	$tab->setColsStyle($type_h);
	$tab->addHead($content_h);
	if(!empty($myprj))
	foreach ($myprj as $key=>$row) {

		$val =$row["id"];

		$progtot=$row["pprog"];
		if ($progtot < 100) {
			$class="prjprogbar_g";
			$img="progbar_g";
		}
		else {
			$class="prjprogbar_r";
			$img="progbar_r";
		}

		$content = array(
			'<a href="index.php?modname=project&amp;op=showprj&amp;id='.$val.'">'.$row["ptitle"].'</a>',
			/* '<img class="'.$class.'" src="'.getPathImage().'prjman/'.$img.'.gif" width="'.( $progtot*2 ).'" alt="'.$lang->def("_PROGRESS").'" />' */
			renderProgress($progtot, 0, 100, false),
			$progtot.'%'
		);
		if ($mod_perm) {
			if ($row["flag"] == 2) {
				$content[] = '<a href="index.php?modname=project&amp;op=manprjadmin&amp;id='.$val.'">'
					.'<img src="'.getPathImage().'standard/moduser.png" '
					.'alt="'.$lang->def("_MODPROJECTADMIN").'" title="'.$lang->def("_MODPROJECTADMIN").'" /></a>';
				$content[] = '<a href="index.php?modname=project&amp;op=modprj&amp;id='.$val.'">'
					.'<img src="'.getPathImage().'standard/edit.png" '
					.'alt="'.$lang->def("_MOD").'" title="'.$lang->def("_MOD").'" /></a>';
			}
			else {
				$content[] ="&nbsp;";
				$content[] ="&nbsp;";
			}
		}
		if ($del_perm) {
			if ($row["flag"] == 2) {
				$content[] = '<a href="index.php?modname=project&amp;op=delprj&amp;id='.$val.'" title="'.$lang->def("_DEL")
						.' : '.strip_tags($row["ptitle"]).'">'
					.'<img src="'.getPathImage().'standard/delete.png" '
					.'alt="'.$lang->def("_DEL").'" title="'.$lang->def("_DEL").'" /></a>';
			}
			else {
				$content[] ="&nbsp;";
			}
		}
		$tab->addBody($content);
	}
	if($del_perm) {

		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=delprj]');
	}

	if(checkPerm('add', true)) {
		$tab->addActionAdd('<a href="index.php?modname=project&amp;op=addprj" title="'.$lang->def("_NEW_PROJECT").'">'
			.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def("_NEW_PROJECT").'" /> '.$lang->def("_NEW_PROJECT").'</a>');
	}
	$out->add($tab->getTable());
	$out->add('</div>');
}


function getUserGrpArray($userid) {

	$user_grp=array();
	$acl=Docebo::user()->getAcl();
	$user_grp=$acl->getUserGroupsST($userid);
	$user_grp[]=getLogUserId();

	return $user_grp;
}


function user_projects($userid) {


	$user_grp=getUserGrpArray($userid);
	$grp_list=implode(",", $user_grp);

	$qtxt ="SELECT id FROM ".$GLOBALS["prefix_lms"]."_prj ";
	$qtxt.="WHERE cid='".$_SESSION["idCourse"]."' AND pgroup IN (".$grp_list.") ";
	$qtxt.="ORDER BY ptitle"; //echo("\n\n<!-- ".$qtxt." -->\n\n");

	$res = array();
	$q=sql_query($qtxt);
	if (($q) && (sql_num_rows($q) > 0)) {
		while ($row=sql_fetch_assoc($q)) {
			$res[]=$row["id"];
		}
	}

	return $res;

	// --------------------------------------
	// OLD:
/*	$res=array();

	$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj WHERE cid='".$_SESSION["idCourse"]."' ORDER BY ptitle;");

	if (($query) && (sql_num_rows($query) > 0)) {
		while ($row=sql_fetch_array($query)) {
			$grpqry=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_coursegroupuser WHERE (idGroup='".$row["pgroup"]."' AND idUser='$userid');");
			if (sql_num_rows($grpqry) > 0) array_push($res, $row["id"]);
		}
	}

	return $res; */
}


function userProjectsList($userid) {

	$user_grp=getUserGrpArray($userid);
	$grp_list=implode(",", $user_grp);

	$qtxt ="SELECT t1.*, t2.flag FROM ".$GLOBALS["prefix_lms"]."_prj as t1 ";
	$qtxt.="LEFT JOIN ".$GLOBALS["prefix_lms"]."_prj_users as t2 ";
	$qtxt.="ON (t1.id=t2.pid AND t2.userid='".$userid."') ";
	$qtxt.="WHERE t1.cid='".$_SESSION["idCourse"]."' AND t1.pgroup IN (".$grp_list.") ";
	$qtxt.="GROUP BY t1.id ORDER BY t1.ptitle"; //echo("\n\n<!-- ".$qtxt." -->\n\n");

	$res = array();
	$q =sql_query($qtxt);
	if (($q) && (sql_num_rows($q) > 0)) {
		while ($row=sql_fetch_assoc($q)) {
			$res[]=$row;
		}
	}

	return $res;
}


function getGroupsForProject(& $lang) {

	$acl_man=Docebo::user()->getAclManager();

	//finding group
	$db_groups = $acl_man->getBasePathGroupST('/lms/course/'.$_SESSION['idCourse'].'/group/', true);
	$groups = array();
	$groups[getLogUserId()] = $lang->def('_YOUONLY');
	while(list($idst, $groupid) = each($db_groups)) {

		$groupid = substr($groupid, strlen('/lms/course/'.$_SESSION['idCourse'].'/group/'));
		if($groupid == 'alluser') {
			$groupid = $lang->def('_ALL');
			$sel = $idst;
		}
		$groups[$idst] = $groupid;
	}

	return $groups;
}


function addprj() {
	checkPerm('add');

	require_once(_base_.'/lib/lib.form.php');
	$form=new Form();

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	//=groups=selection==============================================================
	/* if( $_SESSION['levelCourse'] >= '5' ) {
		$query_group = "
		SELECT idGroup, groupName, description, level, owner
		FROM ".$GLOBALS["prefix_lms"]."_coursegroup
		WHERE idCourse='".$_SESSION['idCourse']."'
		ORDER BY groupName";
	}
	else {
		$query_group = "
		SELECT t1.idGroup, t1.groupName, t1.description, t1.level, t1.owner
		FROM ".$GLOBALS["prefix_lms"]."_coursegroup AS t1, ".$GLOBALS["prefix_lms"]."_coursegroupuser AS t2
		WHERE t1.idGroup = t2.idGroup AND
			t1.idCourse='".$_SESSION['idCourse']."'  AND
			(t1.owner = '".$_SESSION['sesUser']."' OR t2.idUser = '".$_SESSION['sesUser']."')
		GROUP BY t1.idGroup
		ORDER BY t1.groupName";
	} */
	//===============================================================================

	$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));

	$out->add('<div class="std_block">'."\n");

	$url="index.php?modname=project&amp;op=project";
	$out->add(getBackUi($url, $lang->def( '_BACK' )));


	$group_arr=getGroupsForProject($lang);


	$url="index.php?modname=project&amp;op=addprj_now";
	$out->add($form->openForm("project_form", $url));
	$out->add($form->openElementSpace());

	$out->add($form->getTextfield($lang->def("_PTITLE"), "ptitle", "ptitle", 255, ''));

	$out->add($form->getDropdown($lang->def("_PGROUP"),"pgroup","pgroup",	$group_arr));

	$out->add($form->getCheckbox($lang->def("_PSFILES"), "psfiles", "psfiles", 1));
	$out->add($form->getCheckbox($lang->def("_PSTASKS"), "pstasks", "pstasks", 1));
	$out->add($form->getCheckbox($lang->def("_PSNEWS"), "psnews", "psnews", 1));
	$out->add($form->getCheckbox($lang->def("_PSTODO"), "pstodo", "pstodo", 1));
	$out->add($form->getCheckbox($lang->def("_PSMSG"), "psmsg", "psmsg", 1));

	$out->add($form->getHidden("applychanges", "applychanges", 1));

	$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());
	$out->add($form->getButton('save', 'save', $lang->def('_NEW_PROJECT')));
	$out->add($form->getButton('undo', 'undo', $lang->def('_UNDO')));
	$out->add($form->closeButtonSpace());
	$out->add($form->closeForm());

	/*
	$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=addprj_now\">"."\n");

	$out->add("<table>\n");
	$out->add("<tr><td><b>".$lang->def("_PTITLE").":</b>\n");
	$out->add("</td><td><input type=\"text\" id=\"ptitle\" name=\"ptitle\" size=\"40\" />\n");
	$out->add("</td></tr>\n");
	$out->add("<tr><td><b>".$lang->def("_PGROUP").":</b>\n");
	$out->add("</td><td>\n");

	$out->add("<select id=\"pgroup\" name=\"pgroup\">\n");


	foreach ($groups as $group_id=>$group_info) {
		$out->add("<option value=\"".$group_id."\">".$group_info["groupid"]."</option>\n");
	}

	$out->add("</select>\n");

	$out->add("</td></tr>\n");
	$out->add("<tr><td style=\"vertical-align: top;\"><b>".$lang->def("_POPTIONS").":</b>\n");
	$out->add("</td><td>\n");
	$out->add("<input type=\"checkbox\" id=\"psfiles\" name=\"psfiles\" value=\"1\" checked />".$lang->def("_PSFILES")."<br />\n");
	$out->add("<input type=\"checkbox\" id=\"pstasks\" name=\"pstasks\" value=\"1\" checked />".$lang->def("_PSTASKS")."<br />\n");
	$out->add("<input type=\"checkbox\" id=\"psnews\" name=\"psnews\" value=\"1\" checked />".$lang->def("_PSNEWS")."<br />\n");
	$out->add("<input type=\"checkbox\" id=\"pstodo\" name=\"pstodo\" value=\"1\" checked />".$lang->def("_PSTODO")."<br />\n");
	$out->add("<input type=\"checkbox\" id=\"psmsg\" name=\"psmsg\" value=\"1\" checked />".$lang->def("_PSMSG")."<br />\n");
	$out->add("</td></tr>\n");
	$out->add("</table>\n");

	$out->add("<input class=\"button\" type=\"submit\" value=\"".$lang->def("_NEW_PROJECT")."\" />\n");
	$out->add("</form>\n");
	*/
	$out->add("</div>");

}


function in_group($userid, $group) {
	$user_grp=getUserGrpArray($userid);
	return in_array($group, $user_grp);
}


function addprj_now() {
	checkPerm('add');

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	//area title
	$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
	$out->add('<div class="std_block">');

	$err="";
	$ptitle=$_POST["ptitle"];
	$pgroup=$_POST["pgroup"];
	$psfiles=(isset($_POST["psfiles"]) ? $_POST["psfiles"] : 0);
	$pstasks=(isset($_POST["pstasks"]) ? $_POST["pstasks"] : 0);
	$psnews=(isset($_POST["psnews"]) ? $_POST["psnews"] : 0);
	$pstodo=(isset($_POST["pstodo"]) ? $_POST["pstodo"] : 0);
	$psmsg=(isset($_POST["psmsg"]) ? $_POST["psmsg"] : 0);
	$idCourse=$_SESSION["idCourse"];

	if ($ptitle == "") $err=$lang->def("_PRJNOTITLE");
	if (!in_group(Docebo::user()->getIdSt(), $pgroup))
		$err=$lang->def("_PRJNOVALIDGROUP");

	//$backlink="<br /><br /><a href=\"javascript:history.go(-1);\">".$lang->def("_BACK")."</a>\n";
	$url="index.php?modname=project&amp;op=addprj";
	$backlink=getBackUi($url, $lang->def( '_BACK' ));
	$goonlink="<br /><br /><a href=\"index.php?modname=project&amp;op=project\">".$lang->def("_CONTINUE")." &gt;&gt;</a>\n";

	if ($err == "") {
		$query=sql_query("INSERT INTO ".$GLOBALS["prefix_lms"]."_prj (ptitle,pgroup,psfiles,pstasks,psnews,pstodo,psmsg,cid) VALUES('$ptitle','$pgroup','$psfiles','$pstasks','$psnews','$pstodo','$psmsg','$idCourse');");

		if ($query) {
			$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj ORDER BY id DESC;");
			$row=sql_fetch_array($query);
			$id=$row["id"];

			$query=sql_query("INSERT INTO ".$GLOBALS["prefix_lms"]."_prj_users (pid,userid,flag) VALUES('$id','".Docebo::user()->getIdSt()."','2');");
			$out->add( sql_error());

			//$out->add(getResultUi($lang->def("_OPERATION_SUCCESSFUL")).$goonlink);
			Util::jump_to("index.php?modname=project&op=project");
		}
		else
			$out->add(getErrorUi($lang->def("_OPERATION_FAILURE").": ".sql_error()).$backlink);
	}
	else
		$out->add(getErrorUi($lang->def("_OPERATION_FAILURE").": ".$err).$backlink);

	$out->add('</div>');
}


function get_level($user, $prjid) {

	$res=0; // Nessun privilegio speciale; 1=admin 2=owner.

	$qtxt="SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_users WHERE (userid='".$user."' AND pid='".$prjid."');";
	$query=sql_query($qtxt);

	if (($query) && (sql_num_rows($query) > 0)) {
		$row=sql_fetch_array($query);
		$res=$row["flag"];
	}

	return $res;

}


function is_owner($user, $prjid) { // per maggior leggibilita' del codice
	return (boolean)(get_level($user, $prjid) == 2);
}


function is_admin($user, $prjid) { // per maggior leggibilita' del codice
	return (boolean)(get_level($user, $prjid) == 1);
}


function getAdminList($prjid) {

	$res = array();

	$qtxt="SELECT userid FROM ".$GLOBALS["prefix_lms"]."_prj_users WHERE pid='".$prjid."' AND flag='1'";
	$query=sql_query($qtxt);

	if (($query) && (sql_num_rows($query) > 0)) {
		while($row=sql_fetch_array($query)) {
			$res[]=$row["userid"];
		}
	}

	return $res;

}


function show_task( $id, $row, $modimg ) {

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	$out->add('<div class="inline_block">'."\n"
		.'<h2 class="heading">'.$lang->def("_PRJTASKS").'</h2>');

	$query = sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj WHERE id='$id' LIMIT 1;");
	$data = sql_fetch_array($query);
	$progtot = $row["pprog"];

	$out->add('<div class="content">'
		.'<table width="100%">'
		.'<tr><td>'
		.'<b>'.$lang->def("_PRJPROGTOT").':</b></td><td class="progress_td">');
	if ($progtot < 100) {
		$class="prjprogbar_g";
		$img="progbar_g";
	}
	else {
		$class="prjprogbar_r";
		$img="progbar_r";
	}
	/*$out->add("<img class=\"$class\" src=\"".getPathImage()."prjman/$img.gif\" width=\"".($progtot*1.6)."\" alt=\"".$lang->def("_PROGRESS")."\" />\n");*/
	$out->add(renderProgress($progtot, 0, 100, false));
	$out->add("</td><td class=\"align_right\">$progtot%</td>\n");

	$readlink = $modlink=""; $dellink="";
	if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
		$modlink="<a href=\"index.php?modname=project&amp;op=editprogtot&amp;id=".$id."\"><img src=\"".getPathImage()."standard/edit.png\" alt=\"".$lang->def("_MOD")."\" /></a>";
		$dellink="<img src=\"".getPathImage()."standard/delete.png\" alt=\"".$lang->def("_DEL")."\" />";
	}
	$out->add("<td class=\"image\">$modlink</td><td class=\"image\">&nbsp;</td>\n");
	$out->add("</tr>\n");

	$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_tasks WHERE pid='$id' ORDER BY tname;");
	if (($query) && (sql_num_rows($query) > 0)) {
		while ($data=sql_fetch_array($query)) {
			$tdesc=nl2br($data["tdesc"]);
			$tdesc=str_replace("'","\'",$tdesc); $tdesc=str_replace("\"","\\'",$tdesc);
			$tdesc=str_replace("\n","\\n",$tdesc); $tdesc=str_replace("\r","\\r",$tdesc);
			$readlink="<a href=\"index.php?modname=project&amp;op=prjreaditem&amp;type=task&amp;id=$id&amp;itemid=".$data["id"]."\">".$data["tname"]."</a>";
			$out->add("<tr><td><b>".$readlink."</b></td><td class=\"progress_td\">\n");
			/*$out->add("<img src=\"".getPathImage()."prjman/progbar.gif\" class=\"prjprogbar\" width=\"".($data["tprog"]*1.6)."\" alt=\"\" />\n"); */
			$out->add(renderProgress($data["tprog"], 0, 100, false));
			$out->add("</td><td class=\"align_right\">".$data["tprog"]."%</td>\n");
			$readlink = $modlink=""; $dellink="";
			if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
				$modlink="<a href=\"index.php?modname=project&amp;op=prjedititem&amp;type=task&amp;id=$id&amp;itemid=".$data["id"]."\"><img src=\"".getPathImage()."standard/edit.png\" alt=\"\" /></a>";
				$dellink="<a href=\"index.php?modname=project&amp;op=prjdelitem&amp;type=task&amp;id=$id&amp;itemid=".$data["id"]."\"><img src=\"".getPathImage()."standard/delete.png\" alt=\"\" /></a>";
			}
			$out->add("<td class=\"image\">$modlink</td><td class=\"image\">$dellink</td>\n");
			$out->add("</tr>\n");
		}
	} else $out->add(Lang::t('_NO_DATA', 'standard'));
	$out->add("</table>"
			.'</div>');
	if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
		$out->add('<div class="actions">'
			.'<a href="index.php?modname=project&amp;op=prjadditem&amp;type=task&amp;id='.$id.'">'
			.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def("_NEW").'" /> '.$lang->def("_NEW").'</a>'
			.'</div>'."\n");

	}
	$out->add('</div>');
}


function show_news( $id, $row , $modimg ) {

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	$out->add('<div class="inline_block">'."\n"
		.'<h2 class="heading">'.$lang->def("_NEWS").'</h2>'
		.'<div class="content">');

	$query = sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_news WHERE pid='$id' ORDER BY ndate DESC;");
	if (($query) && (sql_num_rows($query) > 0)) {
		$out->add("<table width=\"100%\">\n");
		while ($data=sql_fetch_array($query)) {
			$ndate=Format::date($data["ndate"], "date");
			$modlink=""; $dellink="";

			$out->add("<tr>\n");
			$out->add("<td>$ndate</td>\n");
			$readlink="<a href=\"index.php?modname=project&amp;op=prjreaditem&amp;type=news&amp;id=$id&amp;itemid=".$data["id"]."\">".$data["ntitle"]."</a>";
			$out->add("<td><b>".$readlink."</b></td>");
			if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
				$modlink="<a href=\"index.php?modname=project&amp;op=prjedititem&amp;type=news&amp;id=$id&amp;itemid=".$data["id"]."\"><img src=\"".getPathImage()."standard/edit.png\" alt=\"".$lang->def("_MOD")."\" /></a>";
				$dellink="<a href=\"index.php?modname=project&amp;op=prjdelitem&amp;type=news&amp;id=$id&amp;itemid=".$data["id"]."\"><img src=\"".getPathImage()."standard/delete.png\" alt=\"".$lang->def("_DEL")."\" /></a>";
			}
			$out->add("<td class=\"image\">$modlink</td><td class=\"image\">$dellink\n");
			$out->add("</td></tr>\n");
		}
		$out->add("</table>\n");
	} else $out->add(Lang::t('_NO_DATA', 'standard'));
	$out->add('</div>');
	if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
		$out->add('<div class="actions">'
			.'<a href="index.php?modname=project&amp;op=prjadditem&amp;type=news&amp;id='.$id.'">'
			.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def("_NEW").'" /> '.$lang->def("_NEW").'</a>'
			.'</div>'."\n");
	}
	$out->add('</div>');
}


function show_files( $id, $row , $modimg ) {

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	$out->add('<div class="inline_block">'."\n"
		.'<h2 class="heading">'.$lang->def("_PRJFILES").'</h2>'
		.'<div class="content">');

	$query = sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_files WHERE pid='$id' ORDER BY ftitle;");
	if (($query) && (sql_num_rows($query) > 0)) {
		$out->add('<table width="100%">'."\n");
		while($data = sql_fetch_array($query)) {
			$fname = $data["fname"];
			$img = '<img src="'.getPathImage('fw').mimeDetect(_FPATH.$fname).'" alt="myme-type" />';
			$readlink = $modlink = $dellink = '';

			$readlink = "<a href=\"index.php?modname=project&amp;op=prjreaditem&amp;type=file&amp;id=$id&amp;itemid=".$data["id"]."\">".$data["ftitle"]."</a>";

			$out->add('<tr><td class="image">'."\n"
				.'<a href="index.php?modname=project&amp;op=download&amp;type=file&amp;id='.$data['id'].'">'.$img.'</a>'
				.'</td><td>'."\n"
				.$readlink);
			if (!empty($data["fver"]))
				$out->add( " ".$lang->def("_VERSION")." ".$data["fver"]);
			$out->add( "</td>"
				//.
				."\n");
			if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
				$modlink = "<a href=\"index.php?modname=project&amp;op=prjedititem&amp;type=file&amp;id=$id&amp;itemid=".$data["id"]."\"><img src=\"".getPathImage()."standard/edit.png\" alt=\"".$lang->def("_MOD")."\" /></a>";
				$dellink="<a href=\"index.php?modname=project&amp;op=prjdelitem&amp;type=file&amp;id=$id&amp;itemid=".$data["id"]."\"><img src=\"".getPathImage()."standard/delete.png\" alt=\"".$lang->def("_DEL")."\" /></a>";

			}
			$out->add('<td class="image">'.$modlink.'</td><td class="image">'.$dellink.'</td>'."\n");
			$out->add("</tr>\n");
		}
		$out->add("</table>\n");
	} else $out->add(Lang::t('_NO_DATA', 'standard'));
	$out->add('</div>');
	if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
		$out->add( '<div class="actions">'
			.'<a href="index.php?modname=project&amp;op=prjadditem&amp;type=file&amp;id='.$id.'">'
			.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def("_NEW").'" /> '.$lang->def("_NEW").'</a>'
			.'</div>'."\n");
	}
	$out->add( '</div>');
}

function show_todo( $id, $row , $modimg ) {

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	$out->add('<div class="inline_block">'."\n"
		.'<h2 class="heading">'.$lang->def("_PRJTODO").'</h2>'
		.'<div class="content">');

	$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_todo WHERE pid='$id' ORDER BY id DESC;");
	if (($query) && (sql_num_rows($query) > 0)) {
		$out->add("<table width=\"100%\">\n");
		while ($data=sql_fetch_array($query)) {
			$readlink="<a href=\"index.php?modname=project&amp;op=prjreaditem&amp;type=todo&amp;id=$id&amp;itemid=".$data["id"]."\">".$data["ttitle"]."</a>";
			$out->add("<tr><td><b>".$readlink."</b>");
			$modlink=""; $dellink="";
			if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
				$modlink="<a href=\"index.php?modname=project&amp;op=prjedititem&amp;type=todo&amp;id=$id&amp;itemid=".$data["id"]."\"><img src=\"".getPathImage()."standard/edit.png\" alt=\"".$lang->def("_MOD")."\" /></a>";
				$dellink="<a href=\"index.php?modname=project&amp;op=prjdelitem&amp;type=todo&amp;id=$id&amp;itemid=".$data["id"]."\"><img src=\"".getPathImage()."standard/delete.png\" alt=\"".$lang->def("_DEL")."\" /></a>";
			}
			$out->add('<td class="image">'.$modlink.'</td>'
				.'<td class="image">'.$dellink.'</td>'."\n");
			$out->add("</tr>\n");
		}
		$out->add("</table>\n");
	} else $out->add(Lang::t('_NO_DATA', 'standard'));
	$out->add('</div>');
	if ((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id))) {
		$out->add( '<div class="actions">'
			.'<a href="index.php?modname=project&amp;op=prjadditem&amp;type=todo&amp;id='.$id.'">'
			.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def("_NEW").'" /> '.$lang->def("_NEW").'</a>'
			.'</div>'."\n");
	}
	$out->add( '</div>');
}

function show_prj() {
	checkPerm('view');

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	global $pathprj;
	require_once( _base_.'/lib/lib.table.php' );
	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id = $_GET["id"];
	$myprj = user_projects(Docebo::user()->getIdSt());

	if( !in_array($id, $myprj) )
		die("You can't access");

	$modimg = '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def("_MOD").'" />';

	$query = sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj WHERE id='$id';");
	if (($query) && (sql_num_rows($query) > 0)) {
		$row = sql_fetch_array($query);
	}


	$ta_array=array();
	$ta_array["index.php?modname=project&amp;op=project"]=$lang->def("_PROJECT_MANAGER");
	$ta_array[]=$row["ptitle"];
	$out->add(getTitleArea($ta_array, "project"));
	$out->add('<div class="std_block">');

/*	$out->add('<div class="alignRight">'
		.'<a class="back_comand" href="index.php?modname=project&amp;op=project">'.$lang->def("_BACK").'</a></div><br />'); */

	$url="index.php?modname=project&amp;op=project";
	$out->add(getBackUi($url, $lang->def( '_BACK' )));

	$show_something=false;

	$out->add("<table class=\"prjcontainer\">\n");
	$out->add("<tr>\n");
	$out->add("<td width=\"60%\">\n");

	//=TASKS==============
	if ($row["pstasks"]) {
		show_task( $id, $row, $modimg );
		$show_something=true;
	}

	//=FILES==============
	if ($row["psfiles"]) {
		show_files( $id, $row, $modimg );
		$show_something=true;
	}

	$out->add("</td>\n");
	$out->add("<td>");
	//=NEWS==============
	if ($row["psnews"]) {
		show_news( $id, $row, $modimg );
		$show_something=true;
	}

	//=TODO==============
	if ($row["pstodo"]) {
		show_todo( $id, $row, $modimg );
		$show_something=true;
	}

	$out->add("</td>\n");

	$out->add("</tr>\n");
	$out->add("</table><br />\n");

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=prjdelitem]');
	//=MSG======================================================
	if ($row["psmsg"]) {

		$show_something=true;


		require_once($GLOBALS["where_framework"]."/lib/lib.sysforum.php");

		$out->add('<h3 class="prjmsg_title">'.$lang->def("_MESSAGES").'</h3>'."\n");

		$out=& $GLOBALS['page'];
		$out->setWorkingZone('content');
		//$lang=DoceboLanguage::createInstance("sysforum", "lms");

		$sf=new sys_forum("lms", "project_message", $id);
		$sf->setPrefix($GLOBALS["prefix_lms"]);
		$sf->can_write 		= true;
		$sf->can_moderate 	= (bool)((is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id)));
		$sf->can_upload 	= true;
		$sf->use_realname 	= true;

		$sf->url="index.php?modname=project&amp;op=showprj&amp;id=".$id;

		$out->add($sf->show());

		// Change with sysforum class
	}

	if (!$show_something) {
		$out->add("<h3>".$lang->def("_NOTHINGTOSEE")."</h3>\n");
	}

	$out->add('</div>');
}


function manprjadmin() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.userselector.php');
	require_once(_base_.'/lib/lib.form.php');

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");
	$from = new Form();

	if ((!isset($_GET["id"])) || ($_GET["id"] < 1))
		return 0;

	$id=$_GET["id"];
	$myprj = user_projects(Docebo::user()->getIdSt());

	$view_perm=checkPerm('view', true);

	if(($view_perm) && (in_array($id, $myprj)) && (is_owner(Docebo::user()->getIdSt(), $id))) {

		$aclManager 	= new DoceboACLManager();
		$user_select 	= new UserSelector();

		$user_select->show_user_selector = TRUE;
		$user_select->show_group_selector = FALSE;
		$user_select->show_orgchart_selector = FALSE;
		$user_select->show_fncrole_selector = FALSE;
		$user_select->learning_filter = 'course';

		if(isset($_POST['recipients'])) {
			$recipients = Util::unserialize(urldecode($_POST['recipients']));
		} else {
			$recipients = getAdminList($id);
		}
		$user_select->resetSelection($recipients);

		$back_url="index.php?modname=project&amp;op=project";

		if (isset($_POST["cancelselector"])) {
			Util::jump_to(str_replace("&amp;", "&", $back_url));
		}
		else if (isset($_POST["okselector"])) {

			$arr_selection=$user_select->getSelection($_POST);
			//$arr_unselected=$user_select->getUnselected();

			foreach($arr_unselected as $userid) {
				$qtxt ="DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_users ";
				$qtxt.="WHERE pid='".$id."' AND flag='1' AND userid='".$userid."'";
				$q=sql_query($qtxt);
			}

			foreach($arr_selection as $userid) {
				$qtxt ="INSERT INTO ".$GLOBALS["prefix_lms"]."_prj_users ";
				$qtxt.="(pid,userid,flag) VALUES('".$id."','$userid','1')";
				$q=sql_query($qtxt);
			}

			Util::jump_to(str_replace("&amp;", "&", $back_url));
		}
		else {

			//$user_select->setGroupFilter('path', '/lms/course/'.$_SESSION['idCourse'].'/group');

			$group_all = $aclManager->getGroupST('/lms/course/'.(int)$_SESSION['idCourse'].'/group/alluser');

			$query = "
			SELECT pgroup
			FROM ".$GLOBALS["prefix_lms"]."_prj
			WHERE cid='".$_SESSION["idCourse"]."'
				AND id = '".$id."'";
			list($group) = sql_fetch_array(sql_query($query));
			if($group == $group_all) {
				$arr_idstGroup = $aclManager->getGroupsIdstFromBasePath('/lms/course/'.(int)$_SESSION['idCourse'].'/subscribed/');
				$user_select->setUserFilter('group',$arr_idstGroup);
			} else {
				$user_select->setUserFilter('group', array($group));
			}

			$user_select->setPageTitle(
				getTitleArea(array($back_url => $lang->def('_PROJECT_MANAGER'),
				$lang->def('_PADMINS') ),
				'project', $lang->def('_PROJECT_MANAGER')));
			$user_select->loadSelector('index.php?modname=project&amp;op=manprjadmin&amp;id='.$id,
					false,
					"",
					true);
		}
	}
	else
		die("You can't access");

}

function edit_news($mode="edit") {

	require_once(_base_.'/lib/lib.form.php');
	$form=new Form();

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id=$_GET["id"];
	$itemid= importVar("itemid");
	$myprj=user_projects(Docebo::user()->getIdSt());


	$view_perm=checkPerm('view', true);

	if(($view_perm) && (in_array($id, $myprj)) && ( (is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id)) ) ) {


		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add( '<div class="std_block">');

		if ($mode == "edit") $goto="prjedititem";
		if ($mode == "new") $goto="prjadditem";

		if ( isset($_POST["applychanges"]) ) {
			$ndate=Format::dateDb($_POST["ndate"], "date");
			$ntitle=$_POST["ntitle"];
			$ntxt=$_POST["ntxt"];

			if ($mode == "new") {
				$query=sql_query("INSERT INTO ".$GLOBALS["prefix_lms"]."_prj_news (pid,ntitle,ntxt,ndate) VALUES('$id','$ntitle','$ntxt','$ndate');");
			}
			if ($mode == "edit") {
				$query=sql_query("UPDATE ".$GLOBALS["prefix_lms"]."_prj_news SET ndate='$ndate',ntitle='$ntitle',ntxt='$ntxt' WHERE id='$itemid' LIMIT 1;");
			}
			Util::jump_to(" index.php?modname=project&op=showprj&id=$id");
		}

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));


		if ($mode == "edit") {
			$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_news WHERE pid='$id' AND id='$itemid';");
			if (($query) && (sql_num_rows($query) > 0)) {
				$row=sql_fetch_array($query);
				$ndate=Format::date($row["ndate"], "date");
			}
			$label=$lang->def("_SAVE");
		}
		else if ($mode == "new") {
			$row=Array();
			$label=$lang->def("_SAVE");
		}

		if (empty($ndate))
			$ndate=Format::date(date("Y-m-d"), "date");

		$ntitle=( isset($row["ntitle"]) ? $row["ntitle"] : '' );
		$ntxt=( isset($row["ntxt"]) ? $row["ntxt"] : '' );

		$url="index.php?modname=project&amp;op=$goto&amp;type=news&amp;id=$id&amp;itemid=".$itemid;
		$out->add($form->openForm("form_name", $url));
		$out->add($form->openElementSpace());

		$out->add($form->getDatefield($lang->def("_DATE"), "ndate", "ndate", $ndate));

		$out->add($form->getTextfield($lang->def("_TITLE"), "ntitle", "ntitle", 255, $ntitle));

		$out->add($form->getTextarea($lang->def("_TEXTOF"), "ntxt", "ntxt", $ntxt));

		$out->add($form->getHidden("applychanges", "applychanges", 1));

		$out->add($form->closeElementSpace());
		$out->add($form->openButtonSpace());
		$out->add($form->getButton('save', 'save', $label));
		$out->add($form->getButton('undo', 'undo', $lang->def('_UNDO')));
		$out->add($form->closeButtonSpace());
		$out->add($form->closeForm());

		$out->add( '</div>');

		return 0;

		$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=$goto&amp;type=news&amp;id=$id&amp;itemid=$itemid\">\n"
		.'<input type="hidden" id="authentic_request_prj" name="authentic_request" value="'.Util::getSignature().'" />');

		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_news WHERE pid='$id' AND id='$itemid';");
		if (($query) && (sql_num_rows($query) > 0) || ($mode == "new")) {
			if ($mode == "edit")
				$row=sql_fetch_array($query);
			if ($mode == "new")
				$row=Array();
			//$out->add("<table border=\"0\" cellpadding=\"4\" cellspacing=\"1\" bgcolor=\"#DDDDEE\" style=\"border-spacing: 1px;\" >\n");
			$i=0;
			$out->add("<table><tr>\n");

			if ($mode == "edit") {
				//$datearr=explode("-",$row["ndate"]);
				$ndate=Format::date($row["ndate"]);
				//$datearr[2]."-".$datearr[1]."-".$datearr[0];
			}
			if ($mode == "new")
				$ndate=Format::date(date("Y-m-d H:i:s"));
			$out->add("<td><input type=\"text\" size=\"10\" id=\"ndate\" name=\"ndate\" value=\"".$ndate."\" /></td>\n");
			$out->add("<td><input type=\"text\" size=\"26\" id=\"ntitle\" name=\"ntitle\" value=\"".( isset($row["ntitle"]) ? $row["ntitle"] : '' )."\" />\n");
			$out->add("</td></tr><tr><td colspan=\"2\"><textarea rows=\"6\" cols=\"30\" id=\"ntxt\" name=\"ntxt\">\n");
			$out->add( (isset($row["ntxt"]) ? $row["ntxt"] : ''));
			$out->add("</textarea><br />\n");
			$out->add("</td></tr></table>\n");

		}

		if ($mode == "edit") $label=$lang->def("_SAVE");
		if ($mode == "new")  $label=$lang->def("_SAVE");

		$out->add("<input type=\"hidden\" id=\"applychanges\" name=\"applychanges\" value=\"1\" />\n");
		$out->add("<input class=\"button\" type=\"submit\" value=\"".$label."\" />\n");
		$out->add("</form>\n");

//		$out->add("<div align=\"center\"><b>[ <a href=\"index.php?modname=project&amp;op=showprj&amp;id=$id\">".$lang->def("_BACK")."</a> ]</b></div>\n");

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$out->add( '<br /></div>');


	}
	else
		die( "You can't access");
}


function edit_todo($mode="edit") {

	require_once(_base_.'/lib/lib.form.php');
	$form=new Form();

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id=$_GET["id"];
	$itemid= importVar('itemid');
	$myprj=user_projects(Docebo::user()->getIdSt());


	$view_perm=checkPerm('view', true);

	if(($view_perm) && (in_array($id, $myprj)) && ( (is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id)) ) ) {


		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add( '<div class="std_block">');

		if ($mode == "edit") $goto="prjedititem";
		if ($mode == "new") $goto="prjadditem";

		if (isset($_POST["applychanges"])) {
			$ttitle=$_POST["ttitle"];
			$ttxt=$_POST["ttxt"];

			if ($mode == "new") {
				$query=sql_query("INSERT INTO ".$GLOBALS["prefix_lms"]."_prj_todo (pid,ttitle,ttxt) VALUES('$id','$ttitle','$ttxt');");
			}
			if ($mode == "edit") {
				$query=sql_query("UPDATE ".$GLOBALS["prefix_lms"]."_prj_todo SET ttitle='$ttitle',ttxt='$ttxt' WHERE id='$itemid' LIMIT 1;");
			}
			@Util::jump_to(" index.php?modname=project&op=showprj&id=$id");
		}


		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));


		if ($mode == "edit") {
			$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_todo WHERE pid='$id' AND id='$itemid';");
			if (($query) && (sql_num_rows($query) > 0)) {
				$row=sql_fetch_array($query);
			}
			$label=$lang->def("_SAVE");
		}
		else if ($mode == "new") {
			$row=Array();
			$label=$lang->def("_SAVE");
		}

		$ttitle=( isset($row["ttitle"]) ? $row["ttitle"] : '' );
		$ttxt=( isset($row["ttxt"]) ? $row["ttxt"] : '' );

		$url="index.php?modname=project&amp;op=$goto&amp;type=todo&amp;id=$id&amp;itemid=".$itemid;
		$out->add($form->openForm("form_name", $url));
		$out->add($form->openElementSpace());

		$out->add($form->getTextfield($lang->def("_TITLE"), "ttitle", "ttitle", 255, $ttitle));

		$out->add($form->getSimpleTextarea($lang->def("_DESCRIPTION"), "ttxt", "ttxt", $ttxt));

		$out->add($form->getHidden("applychanges", "applychanges", 1));

		$out->add($form->closeElementSpace());
		$out->add($form->openButtonSpace());
		$out->add($form->getButton('save', 'save', $label));
		$out->add($form->getButton('undo', 'undo', $lang->def('_UNDO')));
		$out->add($form->closeButtonSpace());
		$out->add($form->closeForm());

		$out->add( '</div>');

		return 0;

		$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=$goto&amp;type=todo&amp;id=$id&amp;itemid=$itemid\">\n"
		.'<input type="hidden" id="authentic_request_prj" name="authentic_request" value="'.Util::getSignature().'" />');

		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_todo WHERE pid='$id' AND id='$itemid';");
		if (($query) && (sql_num_rows($query) > 0) || ($mode == "new")) {

			if ($mode == "edit")
				$row=sql_fetch_array($query);
			if ($mode == "new")
				$row=Array();

			$out->add("<table><tr>\n");
			$out->add("<td><input type=\"text\" size=\"40\" id=\"ttitle\" name=\"ttitle\" value=\"".( isset($row["ttitle"]) ? $row["ttitle"] : '' )."\" /></td>\n");
			$out->add("</tr><tr><td colspan=\"2\"><textarea rows=\"6\" cols=\"30\" id=\"ttxt\" name=\"ttxt\">\n");
			$out->add( isset($row['ttxt']) ? $row["ttxt"] : '' );
			$out->add("</textarea>\n");
			$out->add("</td></tr></table><br />\n");

		}

		if ($mode == "edit") $label=$lang->def("_SAVE");
		if ($mode == "new")  $label=$lang->def("_SAVE");

		$out->add("<input type=\"hidden\" id=\"applychanges\" name=\"applychanges\" value=\"1\" />\n");
		$out->add("<input class=\"button\" type=\"submit\" value=\"".$label."\" />\n");
		$out->add("</form><br />\n");

		//$out->add("<div align=\"center\"><b>[ <a href=\"index.php?modname=project&amp;op=showprj&amp;id=$id\">".$lang->def("_BACK")."</a> ]</b></div>\n");

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$out->add( '</div>');


	}
	else
		die( "You can't access");
}


function edit_tasks($mode="edit") {

	require_once(_base_.'/lib/lib.form.php');
	$form=new Form();

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id=$_GET["id"];
	$itemid = importVar("itemid");
	$myprj=user_projects(Docebo::user()->getIdSt());


	$view_perm=checkPerm('view', true);

	if(($view_perm) && (in_array($id, $myprj)) && ( (is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id)) ) ) {


		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add( '<div class="std_block">');

		if ($mode == "edit") $goto="prjedititem";
		if ($mode == "new") $goto="prjadditem";

		if (isset($_POST["applychanges"])) {
			$tname=$_POST["tname"];
			$tdesc=$_POST["tdesc"];
			$tprog=(int)$_POST["tprog"];

			if ($mode == "new") {
				$query=sql_query("INSERT INTO ".$GLOBALS["prefix_lms"]."_prj_tasks (pid,tname,tprog,tdesc) VALUES('$id','$tname','$tprog','$tdesc');");
			}
			if ($mode == "edit") {
				$query=sql_query("UPDATE ".$GLOBALS["prefix_lms"]."_prj_tasks SET tprog='$tprog',tname='$tname',tdesc='$tdesc' WHERE id='$itemid' LIMIT 1;");
			}
			@Util::jump_to(" index.php?modname=project&op=showprj&id=$id");
		}

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		if ($mode == "edit") {
			$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_tasks WHERE pid='$id' AND id='$itemid';");
			if (($query) && (sql_num_rows($query) > 0)) {
				$row=sql_fetch_array($query);
			}
			$label=$lang->def("_SAVE");
		}
		else if ($mode == "new") {
			$row=Array();
			$label=$lang->def("_SAVE");
		}

		$tname=( isset($row["tname"]) ? $row["tname"] : '' );
		$tprog=( isset($row["tprog"]) ? $row["tprog"] : '' );
		$tdesc=( isset($row["tdesc"]) ? $row["tdesc"] : '' );

		$url="index.php?modname=project&amp;op=$goto&amp;type=task&amp;id=$id&amp;itemid=".$itemid;
		$out->add($form->openForm("form_name", $url));
		$out->add($form->openElementSpace());

		$out->add($form->getTextfield($lang->def("_TASKNAME"), "tname", "tname", 255, $tname));
		$out->add($form->getTextfield($lang->def("_TASKPROGRESS"), "tprog", "tprog", 3, $tprog));

		$out->add($form->getSimpleTextarea($lang->def("_TASKDESC"), "tdesc", "tdesc", $tdesc));

		$out->add($form->getHidden("applychanges", "applychanges", 1));

		$out->add($form->closeElementSpace());
		$out->add($form->openButtonSpace());
		$out->add($form->getButton('save', 'save', $label));
		$out->add($form->getButton('undo', 'undo', $lang->def('_UNDO')));
		$out->add($form->closeButtonSpace());
		$out->add($form->closeForm());

		$out->add( '</div>');

		return 0;

		$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=$goto&amp;type=task&amp;id=$id&amp;itemid=$itemid\">\n"
		.'<input type="hidden" id="authentic_request_prj" name="authentic_request" value="'.Util::getSignature().'" />');

		// progresso totale: ___%
/*		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj WHERE id='$id' LIMIT 1;");
		$row=sql_fetch_array($query);
		$out->add($lang->def("_PRJPROGTOT").":\n");
		$out->add("<input type=\"text\" size=\"3\" id=\"progtot\" name=\"progtot\" value=\"".(int)$row["pprog"]."\" />%<br /><br />\n");*/

		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_tasks WHERE pid='$id' AND id='$itemid';");
		if (($query) && (sql_num_rows($query) > 0) || ($mode == "new")) {

			if ($mode == "edit")
				$row=sql_fetch_array($query);
			if ($mode == "new")
				$row=Array();

			$out->add("<table><tr>\n");
			$out->add("<td><input type=\"text\" size=\"30\" id=\"tname\" name=\"tname\" value=\"".( isset($row["tname"]) ? $row["tname"] : '' )."\" /></td>\n");
			$out->add("<td><input type=\"text\" size=\"3\" id=\"tprog\" name=\"tprog\" value=\"".( isset($row["tprog"]) ? $row["tprog"] : '' )."\" />%</td>\n");
			$out->add("</tr><tr><td colspan=\"2\"><textarea rows=\"6\" cols=\"30\" id=\"tdesc\" name=\"tdesc\">\n");
			$out->add( ( isset($row["tdesc"]) ? $row["tdesc"] : '' ));
			$out->add("</textarea>\n");
			$out->add("</td></tr></table><br />\n");

		}

		if ($mode == "edit") $label=$lang->def("_SAVE");
		if ($mode == "new")  $label=$lang->def("_SAVE");

		$out->add("<input type=\"hidden\" id=\"applychanges\" name=\"applychanges\" value=\"1\" />\n");
		$out->add("<input class=\"button\" type=\"submit\" value=\"".$label."\" />\n");
		$out->add("</form><br />\n");

		//$out->add("<div align=\"center\"><b>[ <a href=\"index.php?modname=project&amp;op=showprj&amp;id=$id\">".$lang->def("_BACK")."</a> ]</b></div>\n");

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$out->add( '</div>');


	}
	else
		die( "You can't access");
}


function edit_files($mode="edit") {

	require_once(_base_.'/lib/lib.upload.php' );
	require_once(_base_.'/lib/lib.form.php');
	$form=new Form();

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id = $_GET["id"];
	$itemid = importVar("itemid");
	$myprj = user_projects(Docebo::user()->getIdSt());


	$view_perm=checkPerm('view', true);

	if(($view_perm) && (in_array($id, $myprj)) && ( (is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id)) ) ) {

		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add( '<div class="std_block">');


		if ($mode == "edit") $goto="prjedititem";
		if ($mode == "new") $goto="prjadditem";

		if ( isset($_POST["applychanges"]) ) {
			$ftitle = $_POST["ftitle"];
			$fver = $_POST["fver"];
			$fdesc = $_POST["fdesc"];

			if ($mode == "new") {
				$ok=1;

				//save file
				sl_open_fileoperations();

				if ((!isset($_FILES['attach'])) || ($_FILES['attach']['name'] == ''))
					$savefile = '';
				else {
					$savefile = $_SESSION['idCourse'].'_'.mt_rand(0,100).'_'.time().'_'.$_FILES['attach']['name'];
					if(!file_exists (_FPATH_INTERNAL.$savefile)) {
						if(!sl_upload($_FILES['attach']['tmp_name'], _FPATH_INTERNAL.$savefile))
						{
							$savefile = '';
							$ok=0;
						}
					}
					else {
						$savefile = '';
						$ok=0;
					}
				}

				sl_close_fileoperations();

				if (($ok) && ($savefile != ""))
					$query=sql_query("INSERT INTO ".$GLOBALS["prefix_lms"]."_prj_files (pid,fname,ftitle,fver,fdesc) VALUES('$id','".( get_magic_quotes_gpc() ? $savefile : sql_escape_string($savefile) )."','$ftitle','$fver','$fdesc');");
			}
			if ($mode == "edit") {
				$query=sql_query("UPDATE ".$GLOBALS["prefix_lms"]."_prj_files SET ftitle='$ftitle',fver='$fver',fdesc='$fdesc' WHERE id='$itemid' LIMIT 1;");
			}

			Util::jump_to(" index.php?modname=project&op=showprj&id=$id");
		}

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));


		if ($mode == "edit") {
			$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_files WHERE pid='$id' AND id='$itemid';");
			if (($query) && (sql_num_rows($query) > 0)) {
				$row=sql_fetch_array($query);
			}
			$label=$lang->def("_SAVE");
		}
		else if ($mode == "new") {
			$row=Array();
			$label=$lang->def("_SAVE");
		}

		$ftitle=( isset($row["ftitle"]) ? $row["ftitle"] : '' );
		$fver=( isset($row["fver"]) ? $row["fver"] : '' );
		$fdesc=( isset($row["fdesc"]) ? $row["fdesc"] : '' );

		$url="index.php?modname=project&amp;op=$goto&amp;type=file&amp;id=$id&amp;itemid=".$itemid;
		$out->add($form->openForm("form_name", $url, "", "", "multipart/form-data"));
		$out->add($form->openElementSpace());

		if ($mode == "new")
			$out->add($form->getFilefield($lang->def("_FILE"), "attach", "attach"));

		$out->add($form->getTextfield($lang->def("_TITLE"), "ftitle", "ftitle", 255, $ftitle));
		$out->add($form->getTextfield($lang->def("_VERSION"), "fver", "fver", 255, $fver));

		$out->add($form->getSimpleTextarea($lang->def("_DESCRIPTION"), "fdesc", "fdesc", $fdesc));

		$out->add($form->getHidden("applychanges", "applychanges", 1));

		$out->add($form->closeElementSpace());
		$out->add($form->openButtonSpace());
		$out->add($form->getButton('save', 'save', $label));
		$out->add($form->getButton('undo', 'undo', $lang->def('_UNDO')));
		$out->add($form->closeButtonSpace());
		$out->add($form->closeForm());


		$out->add( '<br /></div>');

		return 0;

		$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=$goto&amp;type=file&amp;id=$id&amp;itemid=$itemid\" enctype=\"multipart/form-data\">\n"
		.'<input type="hidden" id="authentic_request_prj" name="authentic_request" value="'.Util::getSignature().'" />');

		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_files WHERE pid='$id' AND id='$itemid';");
		if (($query) && (sql_num_rows($query) > 0) || ($mode == "new")) {

			if ($mode == "edit")
				$row=sql_fetch_array($query);
			if ($mode == "new")
				$row=Array();

			$out->add("<table><tr>\n");
			if ($mode != "edit")
				$out->add($lang->def("_FILE").":<br /><input type=\"file\" id=\"attach\" name=\"attach\" size=\"50\" /><br /><br />\n");

			$out->add($lang->def("_TITLE").": <input type=\"text\" size=\"40\" id=\"ttitle\" name=\"ftitle\" value=\""
				.( isset($row["ftitle"]) ? $row["ftitle"] : '' )."\" /></td>\n");

			$out->add("</tr><tr><td>".$lang->def("_VERSION")."\n ");
			$out->add("<input type=\"text\" size=\"35\" id=\"fver\" name=\"fver\" value=\""
				.( isset($row["fver"]) ? $row["fver"] : '' )."\" /></td>\n");
			$out->add("</tr><tr><td colspan=\"2\"><textarea rows=\"6\" cols=\"30\" id=\"fdesc\" name=\"fdesc\">\n");
			$out->add( ( isset($row["fdesc"]) ? $row["fdesc"] : '' ));
			$out->add("</textarea>\n");
			$out->add("</td></tr></table><br />\n");

		}

		if ($mode == "edit") $label=$lang->def("_SAVE");
		if ($mode == "new")  $label=$lang->def("_SAVE");

		$out->add("<input type=\"hidden\" id=\"applychanges\" name=\"applychanges\" value=\"1\" />\n");
		$out->add("<input class=\"button\" type=\"submit\" value=\"".$label."\" />\n");
		$out->add("</form><br />\n");

		//$out->add("<div align=\"center\"><b>[ <a href=\"index.php?modname=project&amp;op=showprj&amp;id=$id\">".$lang->def("_BACK")."</a> ]</b></div>\n");

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$out->add( '<br /></div>');


	}
	else
		$out->add( "You can't access");
}

function send_msg() {
	global $pathprj;
	//require_once( 'core/upload.php' );
	require_once(_base_.'/lib/lib.upload.php');
	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id = $_GET["id"];
	$myprj = user_projects(Docebo::user()->getIdSt());

/*
	if((//-TP// funAccess("project","OP",true)) && (in_array($id, $myprj))) {


		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add( '<div class="std_block">');

		if ( isset($_POST["addmsg"]) ) {

			$ok=1;

			//save file
			sl_open_fileoperations();
			if($_FILES['attach']['name'] == '') $savefile = '';
			else {
				$savefile = $_SESSION['idCourse'].$lang->def("_").rand(0,100).$lang->def("_").time().$lang->def("_").$_FILES['attach']['name'];
				if(!file_exists ($pathprj.$savefile)) {
					if(!sl_upload($_FILES['attach']['tmp_name'], $pathprj.$savefile))
					{
						sl_close_fileoperations();
						$savefile = '';
						$out->add( '<div class="errorBlock">'.$lang->def("_ERROR_UPLOAD").'</div><br />');
						$ok=0;
					}
				}
				else {
					sl_close_fileoperations();
					$savefile = '';
					$out->add( '<div class="errorBlock">'.$lang->def("_ERROR_UPLOAD").'</div><br />');
					$ok=0;
				}
			}
			sl_close_fileoperations();
			if ($ok) {
				$mdate=date("Y-m-d");
				$mfrom=$_POST["mfrom"];
				$msub=$_POST["msub"];
				$mtxt=$_POST["mtxt"];
				$mid=$_POST["mid"];
				$query=sql_query("INSERT INTO ".$GLOBALS["prefix_lms"]."_prj_msg (pid,mid,mfrom,mdate,msub,mfile,mtxt) VALUES('$id','$mid','$mfrom','$mdate','$msub','$savefile','$mtxt');");
				Util::jump_to(" index.php?modname=project&op=showprj&id=$id");
			}
		}

		$mid="";
		if ( isset($_POST['replyto']) && ($_POST["replyto"] > 0)) {
			$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_msg WHERE id='".$_POST["replyto"]."' AND pid='$id';");
			if (($query) && (sql_num_rows($query) > 0)) {
					$row=sql_fetch_array($query);
					$title=$row["msub"];
					if (substr($title, 0, 3) != "Re:") $title="Re: ".$title;
					$text="> ".$row["mtxt"];
					$text=str_replace("\n", "\n> ", $text);
					$mid=$row["mid"];
			}
		}

		if ($mid == "") {
			$mid=Docebo::user()->getIdSt()."-".time().chr(rand(65,90)).chr(rand(65,90));
		}

		$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=prjsendmsg&amp;id=$id\" enctype=\"multipart/form-data\">\n");

		$out->add("<b>".$lang->def("_PRJMSGSUB").":</b><br />\n");
		$out->add("<input type=\"text\" size=\"60\" id=\"msub\" name=\"msub\" value=\"".( isset($title) ? $title : '' )."\" /><br /><br />\n");

		$out->add("<textarea rows=\"6\" cols=\"45\" id=\"mtxt\" name=\"mtxt\">\n");
		$out->add( isset($text) ? $text : '' );
		$out->add("</textarea><br /><br />\n");

		$out->add("<b>".$lang->def("_PRJMSGATTACH").":</b><br />\n");
		$out->add("<input type=\"file\" id=\"attach\" name=\"attach\" size=\"50\" /><br /><br />\n");

		$out->add("<input type=\"hidden\" id=\"mfrom\" name=\"mfrom\" value=\"".Docebo::user()->getIdSt()."\" />\n");
		$out->add("<input type=\"hidden\" id=\"mid\" name=\"mid\" value=\"$mid\" />\n");
		$out->add("<input type=\"hidden\" id=\"addmsg\" name=\"addmsg\" value=\"1\" />\n");
		$out->add("<input class=\"button\" type=\"submit\" value=\"".$lang->def("_PRJMSGSEND")."\" />\n");
		$out->add("</form><br />\n");

//		$out->add("<div align=\"center\"><b>[ <a href=\"index.php?modname=project&amp;op=showprj&amp;id=$id\">".$lang->def("_BACK")."</a> ]</b></div>\n");

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$out->add( '</div>');


	}
	else $out->add( "You can't access");		 */
}


function read_msg() { 	/*
	global $pathprj;
	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id=$_GET["id"];
	$myprj=user_projects(Docebo::user()->getIdSt());

	if((//-TP// funAccess("project","OP", true)) && (in_array($id, $myprj))) {


		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add( '<div class="std_block">');

		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_msg WHERE id='".$_GET["msgid"]."' AND pid='$id';");
		if (($query) && (sql_num_rows($query) > 0)) {
				$row=sql_fetch_array($query);
				$title=$row["msub"];
				$text=$row["mtxt"];
				$user_query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_user WHERE idUser='".$row["mfrom"]."';");
				$user_data=sql_fetch_array($user_query);
				$darr=explode("-",$row["mdate"]);
				$mdate=$darr[2]."-".$darr[1]."-".$darr[0];
				$mid=$row["mid"];
				$fname=$row["mfile"];
				if ($fname != "") {
					$img="<img src=\"".getPathImage().mimeDetect($pathprj.$fname)."\" alt=\"\" />";
					$filelink = '<a href="index.php?modname=project&amp;op=download&amp;type=msg&amp;id='
						.$row["id"].'">'.$img.'</a>';
				}
				else
					$filelink="";
				$out->add("<div class=\"title\">".$row["msub"]."</div>\n");
				$out->add("<b>".$lang->def("_PRJMSGFROM").":</b> ".$user_data["userid"]."<br />\n");
				$out->add("<b>".$lang->def("_PRJMSGDATE").":</b> ".$mdate."<br /><br />\n");
				$out->add("<div style=\"border: 1px solid #E0E2E0; width: 400px; padding: 4px;\">\n");
				$out->add(nl2br(htmlspecialchars($row["mtxt"]))."</div>\n");
				if ($filelink != "") $out->add("<br /><br /><b>".$lang->def("_PRJMSGATTACH").":</b> $filelink\n");

				// Imposto il messaggio come letto:
				$readarr=(array)explode("-",$row["mread"]);
				if (!in_array(Docebo::user()->getIdSt(), $readarr)) {
					array_push($readarr, Docebo::user()->getIdSt());
					$mread=implode("-", $readarr);
					$query=sql_query("UPDATE ".$GLOBALS["prefix_lms"]."_prj_msg SET mread='$mread' WHERE  id='".$_GET["msgid"]."' AND pid='$id';");
				}

		}



		$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=prjsendmsg&amp;id=$id\">\n");

		$out->add("<input type=\"hidden\" id=\"replyto\" name=\"replyto\" value=\"".$_GET["msgid"]."\" />\n");
		$out->add("<br /><input class=\"button\" type=\"submit\" value=\"".$lang->def("_PRJMSGREPLY")."\" />\n");
		$out->add("</form><br />\n");

//		$out->add("<div align=\"center\"><b>[ <a href=\"index.php?modname=project&amp;op=showprj&amp;id=$id\">".$lang->def("_BACK")."</a> ]</b></div>\n");

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$out->add( '<br /></div>');


	}
	else $out->add( "You can't access");		*/
}


function sel_prj($goto) { /*
	if(//-TP// funAccess("progetti","OP")) {



	$out->add("<div class=\"contentBox\">\n");
	$out->add("<div class=\"titleSection\">\n");

	$out->add("<h2>".$lang->def("_OWNEDPRJ")."</h2>\n");

	$myprj=user_projects(Docebo::user()->getIdSt());

	/*$out->add("<pre>\n");
	print_r($myprj);
	$out->add("</pre>\n");*/
/*
	$out->add("<ul>");
	foreach ($myprj as $key=>$val) {
		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj WHERE id='$val';");
		$row=sql_fetch_array($query);
		if (is_owner(Docebo::user()->getIdSt(), $val))
			$out->add("<li><a href=\"index.php?modname=project&amp;op=$goto&amp;id=$val\">".$row["ptitle"]."</li>\n");
	}
	$out->add("</ul>");

	$out->add("</div>\n");
	$out->add("</div>\n");


	}
	else $out->add( "You can't access"); */
}


function mod_prj() {

	require_once(_base_.'/lib/lib.form.php');
	$form=new Form();

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id = $_GET["id"];
	$myprj = user_projects(Docebo::user()->getIdSt());

	$view_perm=checkPerm('mod', true);

	if(($view_perm) && (in_array($id, $myprj)) && (is_owner(Docebo::user()->getIdSt(), $id))) {

		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add('<div class="std_block">');

		if ( isset($_POST["applychanges"]) && ($_POST["ptitle"] != "")) {

			$err="";

			$ptitle=(isset($_POST["ptitle"]) ? $_POST["ptitle"] : "");
			$pgroup=$_POST["pgroup"];
			$old_pgroup=$_POST["old_pgroup"];
			$psfiles=(isset($_POST["psfiles"]) ? $_POST["psfiles"] : 0);
			$pstasks=(isset($_POST["pstasks"]) ? $_POST["pstasks"] : 0);
			$psnews=(isset($_POST["psnews"]) ? $_POST["psnews"] : 0);
			$pstodo=(isset($_POST["pstodo"]) ? $_POST["pstodo"] : 0);
			$psmsg=(isset($_POST["psmsg"]) ? $_POST["psmsg"] : 0);

			$qtxt ="UPDATE ".$GLOBALS["prefix_lms"]."_prj SET ptitle='$ptitle',psfiles='$psfiles',";
			$qtxt.="pstasks='$pstasks',psnews='$psnews',pstodo='$pstodo',psmsg='$psmsg' ";

			if ($pgroup != $old_pgroup) {
				if (in_group(getLogUserId(), $pgroup)) {
					// Removing all admins:
					$pgroup_qtxt ="DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_users ";
					$pgroup_qtxt.="WHERE flag='1' AND pid='".$id."'";

					$q=sql_query($pgroup_qtxt);

					if ($q)
						$qtxt.=",pgroup='".$pgroup."' ";
				}
				else {
					$err=$lang->def("_PRJNOVALIDGROUP");
				}
			}

			$qtxt.="WHERE id='$id' LIMIT 1";

			if (empty($err)) {
				$q=sql_query($qtxt);

				if ($q) {
					//$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
					Util::jump_to("index.php?modname=project&op=project");
				}
			}
			else {
				$out->add(getErrorUi($err));
			}

		}
		//$out->add("<div class=\"alignRight\"><a class=\"back_comand\" href=\"index.php?modname=project&amp;op=project\">".$lang->def("_BACK")."</a></div>\n");


		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj WHERE id='$id';");
		if (($query) && (sql_num_rows($query) > 0)) {
			$row=sql_fetch_array($query);
		}

		$group_arr=getGroupsForProject($lang);

		$url="index.php?modname=project&amp;op=project";
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$url="index.php?modname=project&amp;op=modprj&amp;id=".$id;
		$out->add($form->openForm("project_form", $url));
		$out->add($form->openElementSpace());

		$out->add($form->getTextfield($lang->def("_PTITLE"), "ptitle", "ptitle", 255, $row["ptitle"]));

		$out->add($form->getDropdown($lang->def("_PGROUP"),"pgroup","pgroup",	$group_arr, $row["pgroup"]));
		$out->add($form->getHidden("old_pgroup", "old_pgroup", $row["pgroup"]));

		// TODO: add a fieldset labeled _POPTIONS
		$out->add($form->getCheckbox($lang->def("_PSFILES"), "psfiles", "psfiles", 1, $row["psfiles"]));
		$out->add($form->getCheckbox($lang->def("_PSTASKS"), "pstasks", "pstasks", 1, $row["pstasks"]));
		$out->add($form->getCheckbox($lang->def("_PSNEWS"), "psnews", "psnews", 1, $row["psnews"]));
		$out->add($form->getCheckbox($lang->def("_PSTODO"), "pstodo", "pstodo", 1, $row["pstodo"]));
		$out->add($form->getCheckbox($lang->def("_PSMSG"), "psmsg", "psmsg", 1, $row["psmsg"]));

		$out->add($form->getHidden("applychanges", "applychanges", 1));

		$out->add($form->closeElementSpace());
		$out->add($form->openButtonSpace());
		$out->add($form->getButton('save', 'save', $lang->def('_SAVE')));
		$out->add($form->getButton('undo', 'undo', $lang->def('_UNDO')));
		$out->add($form->closeButtonSpace());
		$out->add($form->closeForm());

		return 0; // OLD FORM:

		$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=modprj&amp;id=$id\">\n"
		.'<input type="hidden" id="authentic_request_prj" name="authentic_request" value="'.Util::getSignature().'" />');

		$out->add("<table>\n");
		$out->add("<tr><td><b>".$lang->def("_PTITLE")."</b>:\n");
		$out->add("</td><td><input type=\"text\" id=\"ptitle\" name=\"ptitle\" size=\"40\" value=\"".$row["ptitle"]."\" />\n");
		$out->add("</td></tr>\n");

		$out->add("<tr><td style=\"vertical-align: top;\"><b>".$lang->def("_POPTIONS").":</b>\n");
		$out->add("</td><td>\n");
		if ($row["psfiles"]) $chk=" checked"; else $chk="";
		$out->add("<input type=\"checkbox\" id=\"psfiles\" name=\"psfiles\" value=\"1\"$chk />".$lang->def("_PSFILES")."<br />\n");
		if ($row["pstasks"]) $chk=" checked"; else $chk="";
		$out->add("<input type=\"checkbox\" id=\"pstasks\" name=\"pstasks\" value=\"1\"$chk />".$lang->def("_PSTASKS")."<br />\n");
		if ($row["psnews"]) $chk=" checked"; else $chk="";
		$out->add("<input type=\"checkbox\" id=\"psnews\" name=\"psnews\" value=\"1\"$chk />".$lang->def("_PSNEWS")."<br />\n");
		if ($row["pstodo"]) $chk=" checked"; else $chk="";
		$out->add("<input type=\"checkbox\" id=\"pstodo\" name=\"pstodo\" value=\"1\"$chk />".$lang->def("_PSTODO")."<br />\n");
		if ($row["psmsg"]) $chk=" checked"; else $chk="";
		$out->add("<input type=\"checkbox\" id=\"psmsg\" name=\"psmsg\" value=\"1\"$chk />".$lang->def("_PSMSG")."<br />\n");
		$out->add("</td></tr>\n");
		$out->add("</table><br />\n");

		$out->add("<input type=\"hidden\" id=\"applychanges\" name=\"applychanges\" value=\"1\" />\n");
		$out->add("<input class=\"button\" type=\"submit\" value=\"".$lang->def("_SAVE")."\" />\n");
		$out->add("</form>\n");



		$out->add('</div>');


	}
	else
		die("You can't access");
}


function del_prj() {
	include_once(_base_.'/lib/lib.form.php');
	include_once(_base_.'/lib/lib.upload.php');

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");
	$form=new Form();

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id=$_GET["id"];
	$myprj=user_projects(Docebo::user()->getIdSt());

	$view_perm=checkPerm('del', true);

	if(($view_perm) && (in_array($id, $myprj)) && (is_owner(Docebo::user()->getIdSt(), $id))) {

		$back_url="index.php?modname=project&amp;op=project";

		if (isset($_POST["undo"])) {
			Util::jump_to($back_url);
		}
		else if (isset($_POST["conf_del"]) || isset($_GET['confirm'])) {

			del_prj_now($id);

			Util::jump_to($back_url);
		}
		else {

			$qtxt="SELECT ptitle FROM ".$GLOBALS["prefix_lms"]."_prj WHERE id='".$id."'";
			$q=sql_query($qtxt);

			if (($q) && (sql_num_rows($q) > 0)) {
				$row=sql_fetch_array($q);
				$title=$row["ptitle"];
			}

			$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));

			$out->add("<div class=\"std_block\">\n");


			$url="index.php?modname=project&amp;op=delprj&amp;id=".$id;

			$out->add($form->openForm("project_form", $url));


			$out->add(getDeleteUi(
			$lang->def('_AREYOUSURE'),
				'<span class="text_bold">'.$lang->def('_TITLE').' :</span> '.$title.'<br />',
				false,
				'conf_del',
				'undo'));

			$out->add($form->closeForm());
			$out->add("</div>\n");
		}

	}
	else
		die("You can't access!");
}


function del_prj_now($id) {
	require_once(_base_.'/lib/lib.upload.php');

	// -------------------------------------- Cancello i messaggi:
	$qtxt ="DELETE FROM ".$GLOBALS["prefix_lms"]."_sysforum ";
	$qtxt.="WHERE key1='project_message' AND key2='".$id."'";
	$query=sql_query($qtxt);
	// ------------------------------------------------------------

	// -------------------------------------- Cancello i file:
	$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_files WHERE pid='$id';");
	if (($query) && (sql_num_rows($query) > 0)) { // cancello allgeati
		while($row=sql_fetch_array($query)) {
			@sl_unlink(_FPATH_INTERNAL.$row["fname"]);
		}
	} // Cancello le righe dal database:
	$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_files WHERE pid='$id';");
	// ------------------------------------------------------------

	// -------------------------------------- Cancello i tasks:
	// Cancello le righe dal database:
	$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_tasks WHERE pid='$id';");
	// ------------------------------------------------------------

	// -------------------------------------- Cancello le news:
	// Cancello le righe dal database:
	$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_news WHERE pid='$id';");
	// ------------------------------------------------------------

	// -------------------------------------- Cancello le cose da fare:
	// Cancello le righe dal database:
	$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_todo WHERE pid='$id';");
	// ------------------------------------------------------------

	// -------------------------------------- Cancello i flag utente:
	// Cancello le righe dal database:
	$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_users WHERE pid='$id';");
	// ------------------------------------------------------------

	// -------------------------------------- Cancello le info del progetto:
	// Cancello le righe dal database:
	$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj WHERE id='$id' LIMIT 1;");
	// ------------------------------------------------------------

}



function del_item() {
	include_once(_base_.'/lib/lib.form.php');
	include_once(_base_.'/lib/lib.upload.php');

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");
	$form=new Form();

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id=(int)importVar("id");
	$itemid=(int)importVar("itemid");

	$myprj=user_projects(Docebo::user()->getIdSt());

	$view_perm=checkPerm('view', true);

	if(($view_perm) && (in_array($id, $myprj)) && ( (is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id)) ) ) {

		if (!isset($_GET["type"]))
			return 0;

		$type=$_GET["type"];

		$back_url="index.php?modname=project&amp;op=showprj&amp;id=".$id;

		if (isset($_POST["undo"])) {
			Util::jump_to($back_url);
		}
		else if (isset($_POST["conf_del"]) || isset($_GET['confirm'])) {

			del_item_now($id, $itemid, $type);

			Util::jump_to($back_url);
		}
		else {

			switch($type) {
				case "news": {
					$field="ntitle";
					$table=$GLOBALS["prefix_lms"]."_prj_news";
				} break;
				case "todo": {
					$field="ttitle";
					$table=$GLOBALS["prefix_lms"]."_prj_todo";
				} break;
				case "task": {
					$field="tname";
					$table=$GLOBALS["prefix_lms"]."_prj_tasks";
				} break;
				case "file": {
					$field="ftitle";
					$table=$GLOBALS["prefix_lms"]."_prj_files";
				} break;
			}

			$qtxt="SELECT ".$field." as title FROM ".$table." WHERE id='".$itemid."' AND pid='".$id."'";
			$q=sql_query($qtxt);

			if (($q) && (sql_num_rows($q) > 0)) {
				$row=sql_fetch_array($q);
				$title=$row["title"];
			}

			$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));

			$out->add("<div class=\"std_block\">\n");


			$url="index.php?modname=project&amp;op=prjdelitem&amp;type=".$type."&amp;id=".$id."&amp;itemid=".$itemid;

			$out->add($form->openForm("project_form", $url));


			$out->add(getDeleteUi(
			$lang->def('_AREYOUSURE'),
				'<span class="text_bold">'.$lang->def('_TITLE').' :</span> '.$title.'<br />',
				false,
				'conf_del',
				'undo'));

			$out->add($form->closeForm());
			$out->add("</div>\n");
		}

	}
	else
		die("You can't access!");

}


function del_item_now($id, $itemid, $type) {
	include_once(_base_.'/lib/lib.upload.php');

	switch($type) {
		case "news": {
				$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_news WHERE id='".$itemid."' AND pid='".$id."' LIMIT 1;");
		} break;
		case "todo": {
			$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_todo WHERE id='".$itemid."' AND pid='".$id."' LIMIT 1;");
		} break;
		case "task": {
			$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_tasks WHERE id='".$itemid."' AND pid='".$id."' LIMIT 1;");
		} break;
		case "file": {
			$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_files WHERE id='".$itemid."' AND pid='".$id."'");
			$row=@sql_fetch_array($query);
			@sl_unlink(_FPATH_INTERNAL.$row["fname"]);
			$query=sql_query("DELETE FROM ".$GLOBALS["prefix_lms"]."_prj_files WHERE id='".$itemid."' and pid='".$id."' LIMIT 1;");
		} break;
	}

}


function read_item() {

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id=$_GET["id"];
	$myprj=user_projects(Docebo::user()->getIdSt());

	$view_perm=checkPerm('view', true);

	if(($view_perm) && (in_array($id, $myprj))) {

		if ($_GET["type"] == "task") {
			$table="_prj_tasks";
			$field="tdesc";
		}
		if ($_GET["type"] == "news") {
			$table="_prj_news";
			$field="ntxt";
		}
		if ($_GET["type"] == "todo") {
			$table="_prj_todo";
			$field="ttxt";
		}
		if ($_GET["type"] == "file") {
			$table="_prj_files";
			$field="fdesc";
		}

		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add( '<div class="std_block">');

		$out->add("<div class=\"descr_prj\">\n");

		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"].$table." WHERE pid='$id' AND id='".(int)$_GET["itemid"]."' LIMIT 1;");
		if (($query) && (sql_num_rows($query) > 0)) {
			$row=sql_fetch_array($query);
			$out->add(nl2br($row[$field]));
		}

		$out->add("</div>\n");

		if ($_GET["type"] == "file") {
			$img ='<img src="'.getPathImage().'/standard/download.png" alt="'.$lang->def("_DOWNLOAD").'" title="'.$lang->def("_DOWNLOAD").'" />';
			$url ="index.php?modname=project&amp;op=download&amp;type=file&amp;id=".(int)$_GET["itemid"];
			$out->add('<a href="'.$url.'">'.$img." ".$lang->def("_DOWNLOAD")."</a>\n");
		}

//		$out->add("<div align=\"center\"><b>[ <a href=\"index.php?modname=project&amp;op=showprj&amp;id=$id\">".$lang->def("_BACK")."</a> ]</b></div>\n");

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$out->add( '<br /></div>');


	}
	else
		die( "You can't access");
}


function edit_progtot() {

	$out=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('project', "lms");

	// Controllo che l'utente non cerchi di entrare in progetti a cui non e' iscritto.
	$id=$_GET["id"];
	$itemid = importVar("itemid");
	$myprj=user_projects(Docebo::user()->getIdSt());

	$view_perm=checkPerm('view', true);

	if(($view_perm) && (in_array($id, $myprj)) && ( (is_owner(Docebo::user()->getIdSt(), $id)) || (is_admin(Docebo::user()->getIdSt(), $id)) ) ) {


		//area title
		$out->add(getTitleArea($lang->def("_PROJECT_MANAGER"), "project"));
		$out->add( '<div class="std_block">');

		if ( isset($_POST["applychanges"]) ) {
			$progtot=(int)$_POST["progtot"];
			$query=sql_query("UPDATE ".$GLOBALS["prefix_lms"]."_prj SET pprog='$progtot' WHERE id='$id' LIMIT 1;");
			@Util::jump_to(" index.php?modname=project&op=showprj&id=$id");
		}

		$out->add("<form method=\"post\" action=\"index.php?modname=project&amp;op=editprogtot&amp;id=$id\">\n"
		.'<input type="hidden" id="authentic_request_prj" name="authentic_request" value="'.Util::getSignature().'" />');

		// progresso totale: ___%
		$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj WHERE id='$id' LIMIT 1;");
		$row=sql_fetch_array($query);
		$out->add($lang->def("_PRJPROGTOT").":\n");
		$out->add("<input type=\"text\" size=\"3\" id=\"progtot\" name=\"progtot\" value=\"".(int)$row["pprog"]."\" />%<br /><br />\n");

		$out->add("<input type=\"hidden\" id=\"applychanges\" name=\"applychanges\" value=\"1\" />\n");
		$out->add("<input class=\"button\" type=\"submit\" value=\"".$lang->def("_SAVE")."\" />\n");
		$out->add("</form><br />\n");

//		$out->add("<div align=\"center\"><b>[ <a href=\"index.php?modname=project&amp;op=showprj&amp;id=$id\">".$lang->def("_BACK")."</a> ]</b></div>\n");

		$url="index.php?modname=project&amp;op=showprj&amp;id=".$id;
		$out->add(getBackUi($url, $lang->def( '_BACK' )));

		$out->add( '<br /></div>');

	}
	else
		die( "You can't access");
}


function projectDispatch($op) {

	switch($op) {
		case "project" : {
			project();
		};break;

		case "addprj" : {
			addprj();
		};break;

		case "addprj_now" : {
			if (!isset($_POST["undo"]))
				addprj_now();
			else
				project();
		};break;

		case "showprj" : {
			show_prj();
		};break;

		case "manprjadmin" : {
			manprjadmin();
		};break;

		case "update_admins": {
			update_admins();
		};break;

		case "prjadditem": {
			if (isset($_POST["undo"])) {
				Util::jump_to("index.php?modname=project&op=showprj&id=".$_GET["id"]);
			}
			else {
				if ($_GET["type"] == "news") edit_news("new");
				if ($_GET["type"] == "todo") edit_todo("new");
				if ($_GET["type"] == "task") edit_tasks("new");
				if ($_GET["type"] == "file") edit_files("new");
			}
		};break;

		case "prjedititem": {
			if (isset($_POST["undo"])) {
				Util::jump_to("index.php?modname=project&op=showprj&id=".$_GET["id"]);
			}
			else {
				if ($_GET["type"] == "news") edit_news();
				if ($_GET["type"] == "todo") edit_todo();
				if ($_GET["type"] == "task") edit_tasks();
				if ($_GET["type"] == "file") edit_files();
			}
		};break;

		case "prjdelitem": {
			del_item();
		};break;

		case "prjsendmsg": {
			send_msg();
		};break;

		case "prjreadmsg": {
			read_msg();
		};break;

		case "modprj": {
			if ($_GET["id"] == 0)
				sel_prj("modprj");
			else if (!isset($_POST["undo"]))
				mod_prj($_GET["id"]);
			else
				project();
		};break;

		case "delprj": {
			if ($_GET["id"] == 0)
				sel_prj("delprj");
			else
				del_prj();
		};break;

		case "prjreaditem": {
			read_item();
		};break;

		case "editprogtot": {
			edit_progtot();
		};break;

		case "download" : {

			require_once(_base_.'/lib/lib.download.php');
			$id = importVar('id', true, 0);
			$type = importVar('type');

			$can_view = checkPerm('view', true);
			switch($type) {
				case "file" : {
					$query=sql_query("SELECT * FROM ".$GLOBALS["prefix_lms"]."_prj_msg WHERE pid='$id' $filter ORDER BY $oby $ord, id DESC;");
					list( $pid, $fname, $ftitle ) = sql_fetch_row(sql_query("
					SELECT pid, fname, ftitle
					FROM ".$GLOBALS["prefix_lms"]."_prj_files
					WHERE id = '$id'"));
					$myprj = user_projects(Docebo::user()->getIdSt());

					if( $can_view && in_array($pid, $myprj) ) {

						$expFileName = explode('.', $fname);
						$totPart = count($expFileName) - 1;

						sendFile(_FPATH_INTERNAL, $fname, $expFileName[$totPart], $ftitle);
					}
					else
						die('You can\'t access');
				} break;
			}
		};break;
	}
}



?>
