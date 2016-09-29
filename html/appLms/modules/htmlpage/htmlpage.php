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

// XXX: addpage
function addpage($object_page) {
	checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('htmlpage');
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECT_PAGE'), 'htmlpage')
		.'<script>'."\n"
		.'my_n=1;'."\n"
		.'function addAttachment() {'."\n"
		.'	my_file = "attach"+my_n;'."\n"
		.'	my_filevalue = \'\';'."\n"
		.'	if (document.getElementById(my_file))'."\n"
		.'		my_filevalue = document.getElementById(my_file).value;'."\n"
		.'	my_html = "'.str_replace(array("\r", "\r\n", "\n"), '', addslashes(Form::getFilefield($lang->def('_UPLOAD'), 'attach%%', 'attach%%'))).'";'."\n"
		.'	if (my_filevalue != \'\') {'."\n"
		.'		my_n=my_n+1;'."\n"
		.'		my_html = my_html.replace(/%%/gi,my_n);'."\n"
		.'		newdiv = document.createElement("div");'."\n"
		.'		newdiv.innerHTML = my_html;'."\n"
		.'		my_divhtml = document.getElementById(\'attachment_area\');'."\n"
		//.'		my_oldhtml = my_divhtml.innerHTML;'."\n"
		.'		my_divhtml.appendChild(newdiv);'."\n"
		.'	}'."\n"
		.'}'."\n"
		.'</script>'."\n"
		.'<div class="std_block">'
		.getBackUi( Util::str_replace_once('&', '&amp;', $object_page->back_url).'&amp;create_result=0', $lang->def('_BACK') )
		
		.Form::openForm('pageform', 'index.php?modname=htmlpage&amp;op=inspage', false, false, 'multipart/form-data')
		.Form::openElementSpace()
		.Form::getHidden('back_url', 'back_url', htmlentities(urlencode($object_page->back_url)) )
		
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 150, $lang->def('_TITLE') )
		.Form::getTextarea($lang->def('_TEXTOF'), 'textof', 'textof', $lang->def('_TEXTOF'))
		.'<div id="attachment_area">'
		.Form::getFilefield($lang->def('_UPLOAD'), 'attach1', 'attach1')
		.'</div>'
		.'<a href="javascript:addAttachment();">(+)</a>'
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('addhtmlpage', 'addhtmlpage', $lang->def('_INSERT'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

// XXX:inspage
function inspage() {
	checkPerm('view', false, 'storage');
	
	$back_url = urldecode($_POST['back_url']);
		
	$insert_query = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_htmlpage
	SET title = '".( (trim($_POST['title']) == '') ? Lang::t('_NOTITLE', 'htmlpage', 'lms') : $_POST['title'] )."',
		textof = '".$_POST['textof']."',
		author = '".(int)getLogUserId()."'";
	if(!sql_query($insert_query)) {
		
		$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE', 'htmlpage', 'lms');
		Util::jump_to( $back_url.'&create_result=0' );
	}
	list($idPage) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
	
	if ($_FILES) {
		$n=0;		
		foreach($_FILES as $_FILE) {
			$n++;
			$file = save_file($_FILE);
			if($file) {
				$insert_query = "INSERT INTO ".$GLOBALS['prefix_lms']."_htmlpage_attachment SET file = '".str_replace("'", "\'", $file)."', title = '".str_replace("'", "\'", trim($_FILE['name']))."', idpage = ".$idPage;
				sql_query($insert_query);
			}
		}		
	}

	Util::jump_to( $back_url.'&id_lo='.$idPage.'&create_result=1' );
}

// XXX: modpage
function modpage( $object_page ) {
	checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('htmlpage');
	
	//retriving info
	list($title, $textof) = sql_fetch_row(sql_query("
	SELECT title, textof 
	FROM ".$GLOBALS['prefix_lms']."_htmlpage 
	WHERE idPage = '".$object_page->getId()."'"));
	
	// recuper gli allegati 
	$path = '/appLms/htmlpages/';
	$query = "SELECT * FROM ".$GLOBALS['prefix_lms']."_htmlpage_attachment WHERE idpage = ".$object_page->getId();
	$res = sql_query($query);
	$attachments = array();
	if ($res) {
		while ($row = sql_fetch_assoc($res)) {
			$attachments[] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'file' => $GLOBALS['where_files_relative'].$path.$row['file']
			);
		}
	}
	
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECT_PAGE'), 'htmlpage')
		.'<div class="std_block">'
		.getBackUi( Util::str_replace_once('&', '&amp;', $object_page->back_url).'&amp;mod_result=0', $lang->def('_BACK') )
		
		.Form::openForm('pageform', 'index.php?modname=htmlpage&amp;op=uppage', false, false, 'multipart/form-data')
		.Form::openElementSpace()
		.Form::getHidden('idPage', 'idPage', $object_page->getId())
		.Form::getHidden('back_url', 'back_url', htmlentities(urlencode($object_page->back_url)))
		.'<script>'."\n"
		.'my_n=1;'."\n"
		.'function delAttachment(id) {'."\n"
		.'	document.getElementById(id).style.textDecoration="line-through";'."\n"
		.'	document.getElementById(\'iddelattachment\').value = document.getElementById(\'iddelattachment\').value+\';\'+id'."\n"
		.'}'."\n"
		.'function addAttachment() {'."\n"
		.'	my_file = "attach"+my_n;'."\n"
		.'	my_filevalue = \'\';'."\n"
		.'	if (document.getElementById(my_file))'."\n"
		.'		my_filevalue = document.getElementById(my_file).value;'."\n"
		.'	my_html = "'.str_replace(array("\r", "\r\n", "\n"), '', addslashes(Form::getFilefield($lang->def('_UPLOAD'), 'attach%%', 'attach%%'))).'";'."\n"
		.'	if (my_filevalue != \'\') {'."\n"
		.'		my_n=my_n+1;'."\n"
		.'		my_html = my_html.replace(/%%/gi,my_n);'."\n"
		.'		newdiv = document.createElement("div");'."\n"
		.'		newdiv.innerHTML = my_html;'."\n"
		.'		my_divhtml = document.getElementById(\'attachment_area\');'."\n"
		//.'		my_oldhtml = my_divhtml.innerHTML;'."\n"
		.'		my_divhtml.appendChild(newdiv);'."\n"
		.'	}'."\n"
		.'}'."\n"
		.'</script>'."\n"
		.'<div class="std_block">'
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 150, $title)
		.Form::getTextarea($lang->def('_TEXTOF'), 'textof', 'textof', $textof), 'content');
		
	foreach($attachments as $attachment) {
		$GLOBALS['page']->add("<a id=\"".$attachment['id']."\" href=\"".$attachment['file']."\" target=\"_blank\">".$attachment['title']."</a> (<a href=\"javascript:delAttachment(".$attachment['id'].");\">x</a>)<br/>", 'content');
	}
	$GLOBALS['page']->add('<div id="attachment_area">'
		.Form::getHidden('iddelattachment','iddelattachment','')
		.Form::getFilefield($lang->def('_UPLOAD'), 'attach1', 'attach1')
		.'</div>'
		.'<a href="javascript:addAttachment();">(+)</a>'
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('addhtmlpage', 'addhtmlpage', $lang->def('_SAVE'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'', 'content');
}

// XXX:uppage
function uppage() {
	checkPerm('view', false, 'storage');
	
	$back_url = urldecode($_POST['back_url']);
	
	$insert_query = "
	UPDATE ".$GLOBALS['prefix_lms']."_htmlpage
	SET title = '".( (trim($_POST['title']) == '') ? Lang::t('_NOTITLE', 'htmlpage', 'lms') : $_POST['title'] )."',
		textof = '".$_POST['textof']."'
	WHERE idPage = '".(int)$_POST['idPage']."'";
	if(!sql_query($insert_query)) {
		
		$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE', 'htmlpage', 'lms');
		Util::jump_to( $back_url.'&mod_result=0' );
	}

	if ($_FILES) {
		$n=0;		
		foreach($_FILES as $_FILE) {
			$n++;
			$file = save_file($_FILE);
			if($file) {
				$insert_query = "INSERT INTO ".$GLOBALS['prefix_lms']."_htmlpage_attachment SET file = '".$file."', title = '".trim($_FILE['name'])."', idpage = ".(int)$_POST['idPage'];
				sql_query($insert_query);
			}
		}		
	}
	
	if ($_POST['iddelattachment'] != '') {
		$ids = explode(';',$_POST['iddelattachment']);
		foreach($ids as $id) {
			if ($id) {
				$query = "DELETE FROM learning_htmlpage_attachment WHERE id = ".$id;
				sql_query($query);
			}
		}
	}

	require_once($GLOBALS['where_lms'].'/class.module/track.object.php');
	Track_Object::updateObjectTitle($_POST['idPage'], 'htmlpage', $_POST['title']);
	
	Util::jump_to( $back_url.'&id_lo='.$_POST['idPage'].'&mod_result=1' );
}

function save_file($file) {
	require_once(_base_.'/lib/lib.upload.php');

	$path = '/appLms/htmlpages/';

	if($file['name'] != '') {

		$savefile = $_SESSION['idCourse'].'_'.rand(0,100).'_'.time().'_'.$file['name'];
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

// XXX: switch
switch($GLOBALS['op']) {
	case "inspage" : {
		inspage();
	};break;
	case "uppage" : {
		uppage();
	};break;
}

}

?>