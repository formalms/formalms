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

function organization_categorize(&$treeView, $idItem) {

	$language =(isset($_SESSION['idCourse']) && defined("LMS") ? Docebo::course()->getValue('lang_code') : false);

	$folder = $treeView->tdb->getFolderById( $idItem );
	$data = $folder->otherValues;

	$type =$data[REPOFIELDOBJECTTYPE];


	require_once(_lms_.'/lib/lib.kbres.php');
	$kbres =new KbRes();
	$r_data =$kbres->getResourceFromItem($data[REPOFIELDIDRESOURCE], $type, 'course_lo');

	if ($type == "scormorg" && $r_data['sub_categorize'] == 1) {
		organization_jump_select_sco($treeView, $idItem, $folder, $data, $type);
	}
	else {
		Util::widget('kbcategorize', array(
			'original_name'=>$data[REPOFIELDTITLE],
			'r_item_id'=>$data[REPOFIELDIDRESOURCE],
			'r_type'=>$type,
			'r_env'=>'course_lo',
			'r_env_parent_id'=>(int)$_SESSION['idCourse'],
			'language'=>$language,
			'back_url'=>'index.php?modname=storage&amp;op=display',
			'form_url'=>'index.php?modname=storage&amp;op=display',
			'form_extra_hidden'=>array(
				//'stay_on_categorize'=>1,
				'idItem'=>$idItem,
			),
		));
	}
}


function organization_categorize_sco() {

	$language =(isset($_SESSION['idCourse']) && defined("LMS") ? Docebo::course()->getValue('lang_code') : false);

	$idResource =Get::req('idResource', DOTY_INT, 0);
	$sco_id =Get::req('sco_id', DOTY_INT, 0);
	$idItem =Get::req('idItem', DOTY_INT, 0);
	$scormorg_title =Get::req('scormorg_title', DOTY_STRING, '');

	$back_url ='index.php?modname=storage&amp;op=org_select_sco
		&amp;idResource='.$idResource.'&amp;title='.$scormorg_title;

	$form_url ='index.php?modname=storage&amp;op=org_categorize_sco
		&amp;idResource='.$idResource.'&amp;sco_id='.$sco_id;
$form_url ='index.php?modname=storage&amp;op=display';
	$qtxt ="SELECT idscorm_item, title, identifierref FROM
		".$GLOBALS['prefix_lms']."_scorm_items WHERE idscorm_item='".(int)$sco_id."'
		AND idscorm_organization='".(int)$idResource."'";
	$q =sql_query($qtxt);

	$row =sql_fetch_assoc($q);

	Util::widget('kbcategorize', array(
		'original_name'=>$row['title'],
		'r_item_id'=>(int)$sco_id,
		'scormorg_id'=>(int)$idResource,
		'r_type'=>'scoitem',
		'r_env'=>'course_lo',
		'r_env_parent_id'=>(int)$_SESSION['idCourse'],
		'r_param'=>'chapter='.$row['identifierref'],
		'language'=>$language,
		'back_url'=>$back_url,
		'form_url'=>$form_url,
		'form_extra_hidden'=>array(
			//'stay_on_categorize'=>1,
			'idItem'=>$idItem,
		),
	));

}


function organization_categorize_save(&$treeView, $idItem) {

	require_once(_lms_.'/lib/lib.kbres.php');

	$folder = $treeView->tdb->getFolderById( $idItem );
	$data = $folder->otherValues;

	$res_id =Get::req('res_id', DOTY_INT, 0);
	$name =Get::req('r_name', DOTY_STRING, "");
	$original_name =Get::req('original_name', DOTY_STRING, "");
	$desc =Get::req('r_desc', DOTY_STRING, "");
	$r_item_id =Get::req('r_item_id', DOTY_INT, 0);
	$type =Get::req('r_type', DOTY_STRING, "");
	$env =Get::req('r_env', DOTY_STRING, "");
	$env_parent_id =Get::req('r_env_parent_id', DOTY_INT, 0);
	$param ='';//Get::req('', DOTY_STRING, "");
	$alt_desc ='';
	$lang_id =Get::req('r_lang', DOTY_INT, "");
	$lang_arr =Docebo::langManager()->getAllLangCode();
	$lang =$lang_arr[$lang_id];
	$force_visible =Get::req('force_visible', DOTY_INT, 0);
	$is_mobile =Get::req('is_mobile', DOTY_INT, 0);
	$folders =Get::req('h_selected_folders', DOTY_STRING, "");
	$json_tags =Util::strip_slashes(Get::req('tag_list', DOTY_STRING, "[]"));

	$kbres =new KbRes();
	$res_id =$kbres->saveResource($res_id, $name, $original_name, $desc, $r_item_id,
		$type, $env, $env_parent_id, $param, $alt_desc, $lang, $force_visible, $is_mobile
	);

  $json_tags = str_replace("[", "", $json_tags);
  $json_tags = str_replace("]", "", $json_tags);
  $json_tags = str_replace('"', "", $json_tags);
  $json_tags = str_replace("\\", "", $json_tags);
  $tags_arr = explode(",", $json_tags);

	if ($res_id > 0) {
		$kbres->setResourceTags($res_id, $tags_arr);
		$kbres->assignToFolders($res_id, explode(',', $folders));
	}
}


function organization_jump_select_sco(&$treeView, $idItem, $folder=false, $data=false, $type=false) {
	$idResource =$data[REPOFIELDIDRESOURCE];
	$url ='index.php?modname=storage&op=org_select_sco&idResource='.$idResource;
	$url.='&scormorg_title='.urlencode($data[REPOFIELDTITLE]).'&amp;idItem='.$idItem;
	Util::jump_to($url);
	die();
}


function organization_select_sco() {

	$form_url ="";
	$idResource =Get::req('idResource', DOTY_INT, 0);
	$idItem =Get::req('idItem', DOTY_INT, 0);
	$scormorg_title =Get::req('scormorg_title', DOTY_STRING, '');

	$title_arr=array();
	$title_arr['index.php?modname=storage&amp;op=display']=stripslashes($scormorg_title);
	$title_arr[]=stripslashes($scormorg_title);
	cout(getTitleArea($title_arr));

	cout('<div class="std_block">');
	cout(Form::openForm('sco_res', $form_url));
	cout(getScoItemsTable($idResource, $scormorg_title, $idItem));
	cout(Form::getHidden('sco_selected', 'sco_selected', 1));
	cout(Form::getHidden('scormorg_id', 'scormorg_id', $idResource));
	cout(Form::closeForm());

	$out ='<div class="align-right">';
	$out.='<a href="#" id="subcategorize_switch" class="ico-wt-sprite subs_del"><span>'.
		Lang::t('_CATEGORIZE_WHOLE_OBJECT', 'kb').'</span></a>';
	$out.="</div>\n";

	$body =Form::openForm('add_res', 'index.php?modname=storage&amp;op=display')
		.Form::getHidden('org_categorize_switch_subcat', 'org_categorize_switch_subcat', '1')
		.Form::getHidden('subcategorize_switch', 'subcategorize_switch', '0')
		.Form::getHidden('idItem', 'idItem', (int)$idItem)
		.Form::closeForm();
	$body.=Lang::t('_YOU_WILL_LOSE_PREVIOUS_CATEGORIZATION', 'kb');

	$out.=Util::widget('dialog', array(
		'id' => 'subcategorize_switch_dialog',
		'dynamicContent' => false,
		'dynamicAjaxUrl' => false,
		'directSubmit'=>true,
		'header' => Lang::t('_AREYOUSURE', 'kb'),
		'body' => $body,
		'callback' => 'function() { this.destroy(); }',
		'callEvents' => array(
			array('caller' => 'subcategorize_switch', 'event' => 'click')
		)
	), true);

	cout($out);

	cout('</div>');

}



function getScoItemsTable($id_org, $scormorg_title, $idItem) {

	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(Get::sett('visu_course'));

	$id_org =(int)$id_org;

	$h_type = array('', '', '', '', '', 'image');
	$h_content = array(
		Lang::t('_NAME', 'organization'),
		Lang::t('_TYPE', 'kb'),
		Lang::t('_ENVIRONMENT', 'kb'),
		Lang::t('_LANGUAGE', 'kb'),
		Lang::t('_TAGS', 'kb'),
		Lang::t('_CATEGORIZE', 'kb'),
	);

	$tb->setColsStyle($h_type);
	$tb->addHead($h_content);


	$qry = "SELECT t1.idscorm_item, t1.title ".
		" FROM ".$GLOBALS['prefix_lms']."_scorm_items as t1 ".
		" WHERE t1.idscorm_organization='".$id_org."'
		  AND t1.idscorm_resource != 0
			ORDER BY t1.idscorm_item";

	$q =sql_query($qry);
	$i =0;
	$data =array();
	$sco_arr =array();
	while ($row = sql_fetch_assoc($q)) {

		$sco_id =$row["idscorm_item"];
		$sco_arr[]=$sco_id;

		$data[$i]["idscorm_item"]=$sco_id;
		$data[$i]["title"]=$row['title'];

		$url ='index.php?modname=storage&amp;op=org_categorize_sco&amp;idItem='.$idItem.'
			&amp;idResource='.$id_org.'&amp;sco_id='.$sco_id.'&amp;scormorg_title='.$scormorg_title;
		$data[$i]["url"] =$url;

		$i++;
	}

	require_once(_lms_.'/lib/lib.kbres.php');
	$kbres =new KbRes();
	$categorized_sco =$kbres->getCategorizedResources($sco_arr, "scoitem", "course_lo", true);
	$categorized_sco_id =(!empty($categorized_sco) ? array_keys($categorized_sco) : array());

	foreach ($data as $row) {

		$line = array();

		$sco_id =$row["idscorm_item"];

		$line[] = $row['title'];

		$categorized =false;
		if (in_array($sco_id, $categorized_sco_id)) {
			$res_id =$categorized_sco[$sco_id]['res_id'];
			$line[]=$categorized_sco[$sco_id]['r_type'];
			$line[]=$categorized_sco[$sco_id]['r_env'];
			$line[]=$categorized_sco[$sco_id]['r_lang'];
			$line[]=(isset($categorized_sco['tags'][$res_id]) ? implode(',', $categorized_sco['tags'][$res_id]) : '');
			$categorized =true;
		}
		else {
			array_push($line, '', '', '', '');
		}

		if ($categorized) {
			$img ='<img src="'.getPathImage().'standard/categorize.png"
				alt="'.Lang::t('_CATEGORIZE', 'kb').'"
				title="'.Lang::t('_CATEGORIZE', 'kb').'" />';
			$line[] ='<a href="'.$row['url'].'">'.$img.'</a>';
		}
		else {
			$line[]='<a class="ico-sprite fd_notice" title="'.Lang::t('_NOT_CATEGORIZED', 'kb').'"
				href="'.$row['url'].'"><span>'.
				Lang::t('_NOT_CATEGORIZED', 'kb').'</span></a>';
		}

		$tb->addBody($line);
	}

	return $tb->getTable();
}



function organization_categorize_switch_subcat(&$treeView, $idItem) {
	require_once(_lms_.'/lib/lib.kbres.php');
	$kbres =new KbRes();

	$folder = $treeView->tdb->getFolderById( $idItem );
	$data = $folder->otherValues;
	$type =$data[REPOFIELDOBJECTTYPE];
	$r_data =$kbres->getResourceFromItem($data[REPOFIELDIDRESOURCE], $type, 'course_lo');

	$cat_sub_items =Get::pReq('subcategorize_switch', DOTY_INT);
	$res_id =(int)$r_data['res_id'];
	$r_env_parent_id =(int)$r_data['r_env_parent_id'];

	$kbres->saveResourceSubCategorizePref($res_id, $cat_sub_items);

	if ($cat_sub_items == 1) {
		organization_jump_select_sco($treeView, $idItem, $folder, $data, $type);
		die();
	} else {
		organization_categorize($treeView, $idItem);
	}
}



?>