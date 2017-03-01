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
 * @package admin-library
 * @subpackage interaction
 */
// --------------------------------------------------------------------------------------------------------------------
// Main object

class sys_forum {

	var $can_read=true;
	var $can_write=true;
	var $can_moderate=false;
	var $can_upload=false;

	var $id_msg=NULL;
	var $tk=NULL;
	var $url=NULL;
	var $pb=NULL;

	var $anchor=FALSE;
	var $active="";

	var $use_realname = false;

	/** database prefix */
	var $prefix=NULL;

	/** platform */
	var $platform=NULL;

	/** page writer object */
	var $out=NULL;

	/** language object */
	var $lang=NULL;

	function sys_forum($platform, $key1, $key2=0, $key3=NULL) {

		$this->out=& $GLOBALS["page"];
		$this->lang =& DoceboLanguage::createInstance('sysforum', $platform);

		if ($key1 == "") {
			echo("error: The thread key couldn't be null<br />\n");
		}
		else {
			$this->tk[1]=$key1;
			$this->tk[2]=$key2;
			$this->tk[3]=$key3;
			$this->url=$this->get_current_url();
		}

	}

	function setPrefix($prefix) {
		$this->prefix=$prefix;
	}

	function setAnchor($anchor) {
		$this->anchor=$anchor;
	}

	function getAnchor() {
		if ((!empty($this->anchor)) && ($this->anchor !== FALSE))
			return $this->anchor;
		else
			return FALSE;
	}

	/**
	 * @param string $active is a user defined code to know wich one is active if you use more than one
	 *               sysforum object on the same page.
	 **/
	function setActive($active) {
		$this->active=$active;
	}

	function getActive() {
		return $this->active;
	}

	function isActive() {

		if (!isset($_GET["sf_active"]))
			$res=TRUE;
		else if ((isset($_GET["sf_active"])) && (!empty($_GET["sf_active"])) &&
		         ($_GET["sf_active"] == $this->getActive())) {
			$res=TRUE;
		}
		else
			$res=FALSE;

		return $res;
	}

	function setUseRealname($value) {
		$this->use_realname=$value;
	}

	function getUseRealname() {
		return (bool)$this->use_realname;
	}

	function chk_key() {
		if (!isset($this->tk[1]))
			die("You have to setup the thread key first<br />\n");
	}

	function show($print_out=TRUE) { // displays the thread
		$res="";
		$this->chk_key();

		$perm["can_read"]=$this->can_read;
		$perm["can_write"]=$this->can_write;
		$perm["can_moderate"]=$this->can_moderate;
		$perm["can_upload"]=$this->can_upload;

		if ((isset($_GET["sf_op"])) && $this->isActive())
			$sf_op=$_GET["sf_op"];
		else
			$sf_op="";

		switch ($sf_op) {
			default: {
				$res=$this->message($this->tk, $perm, $this->url, $this->pb);
			} break;

			case "addmessage": {
				$res=$this->addmessage($this->tk, $perm, $this->url, $this->pb);
			} break;

			case "insmessage": {
				if (!isset($_POST["undo"]))
					$this->insmessage($this->tk, $perm, $this->url);
				else
					$res=$this->message($this->tk, $perm, $this->url, $this->pb);
			} break;

			case "modmessage": {
				$res=$this->modmessage($this->tk, $perm, $this->url, $this->pb);
			} break;

			case "upmessage": {
				$res=$this->upmessage($this->tk, $perm, $this->url, $this->pb);
			} break;

			case "lockmessage": {
				$this->lockmessage($this->tk, $perm, $this->url, $this->pb);
			}

			case "download": {
				if ($this->can_read) {
					$this->download_file();
				}
			} break;
		}

		if ($print_out)
			$this->out->add($res);
		else
			return $res;
	}


	function clean() { // removes all messages that have this key
		$this->chk_key();
	}

	function set_status($is_locked) { // set the status of messages [0|1] as unlocked/locked
		$this->chk_key();
	}


	// function get_page_url($id_page=0, $pb=0) { // Used to get the right url of the page wherever you are
	                                          // (admin, index (block) or index (mod.))
		// Usage example:
		/*
				// From index as a block:
				$sf=new sys_forum("my_thread_id");
				$id_page=get_block_id_page($idBlock); // you need to know the id of the current block
				$sf->url=$sf->get_page_url($id_page, $idBlock);

				// From index as a module or from admin:
				$sf=new sys_forum("my_thread_id");
				$sf->url=$sf->get_page_url();

				// If you want you can also avoid to use this function and then set manually $this->url and
				// $this->pb where available.
		*/

	/*	$fn=basename($_SERVER["PHP_SELF"]);
		$url=$this->get_current_url(array("sf_op", "act_op", "pb"));

		switch (strtolower($fn)) {
			case "admin.php": {
				return $url;
			} break;
			case "index.php": {

				if ($_GET["mn"] != "") {
					$this->pb=(int)$_GET["pb"];
					if ($this->pb > 0) {
						$url.="&amp;pb=".$this->pb;
					}
					return $url;
				}
				else {
					if ($id_page == 0) $id_page=getid_page();
					if ($pb != 0) $this->pb=$pb;
					return "index.php?pag=".$id_page;
					// (because.. when you login, for example, the url is not this one)
				}

			} break;
		}

	} */

	function get_current_url($exclude=array()) {

		$fn=basename($_SERVER["PHP_SELF"]);

		$first=1;
		foreach($_GET as $key=>$val) {
			if (!in_array($key, $exclude)) {

				if ($first) $sep="?"; else $sep="&amp;";
				$fn.=$sep.$key."=".$val;
				$first=0;

			}
		}

		return $fn;
	}

	function getUrlExtra() {
		$res="";

		$active=$this->getActive();
		$res.=(!empty($active) ? "&amp;sf_active=".$active : "");
		$res.=($this->getAnchor() ? $this->getAnchor() : "");

		return $res;
	}


	// --------------------------------------------------------------------------------------------------------------------
	// Functions [im]ported from the forum module


	function getAuthorName($user_id, $firstname, $lastname) {

		if($this->getUseRealname() && $lastname.$firstname != '') {
			return $lastname.' '.$firstname;
		}
		else {
			return $user_id;
		}
	}

	// XXX: message
	function message($tk, $perm, $url, $pb) {

		if(!$perm["can_read"]) die("You can't access!");

		require_once(_base_.'/lib/lib.mimetype.php');
		require_once(_base_.'/lib/lib.table.php');

		$ini = importVar('ini', true, 0);

		$acl_man =& Docebo::user()->getAclManager();

		$path = $GLOBALS['where_files_relative'].'/appCore/'.Get::sett('pathphoto');

		$mod_perm 		= $perm["can_moderate"];
		$read_perm 		= $perm["can_read"];
		$write_perm 	= $perm["can_write"];
		$upload_perm 	= $perm["can_upload"];

		$res = "";

		if((int)$pb != 0) $pbtxt = "&amp;pb=$pb";
		else $pbtxt = "";

		// find message
		$qtxt = "
		SELECT idMessage, author, posted, title, textof, attach, locked
		FROM ".$this->prefix."_sysforum AS t1
		WHERE ".get_sql_tk_str($tk, "t1")."
		ORDER BY posted";
		$reMessage = sql_query($qtxt);

		// find info about authors
		$reNumPost = sql_query("
		SELECT author, COUNT(*)
		FROM ".$this->prefix."_sysforum
		WHERE author <> '1'
		GROUP BY author");
		
		$authors = array();
		while( list($id_a, $num_post_a) = sql_fetch_row($reNumPost) ) {

			$authors[$id_a] 	= $id_a;
			$user_post[$id_a] 	= $num_post_a;
		}
		$authors_info =& $acl_man->getUsers($authors);

		$tab = new Table(0);
		$contentH 	= array($this->lang->def("_AUTHOR"), $this->lang->def("_TEXTOF"));
		$typeH 		= array('forum_sender', 'forum_text');
		$tab->setColsStyle($typeH);
		$tab->addHead($contentH);

		while( list($idM,
					$author,
					$posted,
					$title, $textof, $attach, $locked_m) = sql_fetch_row($reMessage)) {

			// message author

			if(isset($authors_info[$author]) && $authors_info[$author][ACL_INFO_AVATAR] != '') $img_size = @getimagesize($path.$authors_info[$author][ACL_INFO_AVATAR]);
			
			$who = '<div class="forum_author">'
					.( isset($authors_info[$author])
						? $this->getAuthorName(	$acl_man->relativeId($authors_info[$author][ACL_INFO_USERID]),
											$authors_info[$author][ACL_INFO_FIRSTNAME],
											$authors_info[$author][ACL_INFO_LASTNAME])
						: Lang::t('_UNKNOWN_AUTHOR', 'sysforum')
					)
					.'</div>'
					.( $authors_info[$author][ACL_INFO_AVATAR] != ''
						? '<img class="forum_avatar'.( $img_size[0] > 150 || $img_size[1] > 150 ? ' image_limit' : '' ).'" src="'.$path.$authors_info[$author][ACL_INFO_AVATAR].'" alt="'.$this->lang->def('_AVATAR').'" />'
						: '' );
					/*.'<div class="forum_numpost">'.$this->lang->def('_NUMPOST').' : '
					.( isset($user_post[$author]) ? $user_post[$author] : 0 )
					.'</div>';*/

			// message text------------------------------------------------

			$posted_datetime = Format::date($posted);
			$posted_time = time();//$GLOBALS["regset"]->ddate->getTimeStamp();

			$mess = '';
			$mess .= '<div class="forum_post_posted">'
					.$this->lang->def("_DATE").' : '.$posted_datetime.' '.$this->loadDistance($posted_time)
					.'</div>';
			if($attach) {
				$mess .= '<div class="forum_post_attach">'
					.'<a href="'.$url.'&amp;sf_op=download&amp;sf_fileid='.$idM.$this->getUrlExtra().'">'
					.$this->lang->def("_ATTACHMENT").' : '
					.'<img src="'.getPathImage('fw').mimeDetect($attach).'" alt="'.$this->lang->def("_ATTACHMENT").'" /></a>'
					.'</div>';
			}
			if(!$locked_m) {
				$mess .='<div class="forum_post_title">'.$this->lang->def("_SUBJECT").' : '.$title.'</div>'
						.'<div class="forum_post_text">'.$textof.'</div>';
				if(isset($authors_info[$author]) && $authors_info[$author][ACL_INFO_SIGNATURE] != '') {
					$mess .= '<div class="forum_post_sign_separator"></div>'
						.'<div class="forum_post_sign">'.$authors_info[$author][ACL_INFO_SIGNATURE].'</div>';
				}
			}
			else $mess.= '<div class="forum_post_locked">'.$this->lang->def("_LOCKEDMESS").'</div>';

			// action-------------------------------------------------------
			$action = "\n".'<div class="forumAction">&nbsp;';

			if($mod_perm) {
				$action .= '<a href="'.$url.'&amp;sf_op=lockmessage'.$pbtxt.'&amp;idMessage='.$idM.$this->getUrlExtra().'">';
				if($locked_m) $action .= '<img src="'.getPathImage().'forum/demoderate.gif" alt="'.$this->lang->def("_FREEMESS").'" />';
				else $action .= '<img src="'.getPathImage().'forum/moderate.gif" alt="'.$this->lang->def("_LOCKMESS").'" />';
				$action .= '</a>';
			}

			if($author == Docebo::user()->getIdSt() && ($write_perm || $mod_perm) && !$locked_m && $author != "1") {
				$action .= ' <a href="'.$url.'&amp;sf_op=modmessage'.$pbtxt.'&amp;idMessage='.$idM.$this->getUrlExtra().'">'
						.'<img src="'.getPathImage().'standard/edit.png" alt="'.$this->lang->def("_MOD").'" title="'.$this->lang->def("_MOD").'" /></a>';
			}
			$action .= '</div>';
			$tab->addBody( array($who, $mess) );
			$tab->addBody( array('', $action) );
		}

		$action_row = '';
		if( (!$locked_m && $write_perm) || $mod_perm ) {

			$url_add=$url.'&amp;sf_op=addmessage'.$pbtxt.$this->getUrlExtra();

			$this->out->add($tab->addActionAdd('<div class="forumAdd"><a href="'.$url_add.'">
				<img src="'.getPathImage().'standard/add.png" title="'.$this->lang->def("_ADDMESSAGE").'" alt="'.$this->lang->def("_ADDMESSAGE").'" /> '
				.$this->lang->def("_ADDMESSAGE").'</a></div>'));
		}
		
		$res.=$tab->getTable();

		return $res;
	}


	// XXX: distance
	function loadDistance( $passed_time ) {

		$distance = time() - $passed_time;
		//second -> minutes
		$distance = (int)($distance / 60);
		//< 1 hour print minutes
		if( ($distance >= 0 ) && ($distance < 60) ) return '( '.$distance.' '.$this->lang->def("_MINUTES").' )';

		//minutes -> hour
		$distance = (int)($distance / 60);
		if( ($distance >= 0 ) && ($distance < 60) ) return '( '.$distance.' '.$this->lang->def("_HOURS").' )';

		//hour -> day
		$distance = (int)($distance / 24);
		if( ($distance >= 0 ) && ($distance < 30 ) ) return '( '.$distance.' '.$this->lang->def("_DAYS").' )';

		//echo > 1 month
		return '( '.$this->lang->def("_ONEMONTH").' )';
	}



	function addmessage($tk, $perm, $url, $pb) {
		$res="";
		//global $activeForumUpload;

		if ((int)$pb != 0) $pbtxt="&amp;pb=$pb"; else $pbtxt="";

		$mod_perm=$perm["can_moderate"];
		$read_perm=$perm["can_read"];
		$write_perm=$perm["can_write"];
		$upload_perm=$perm["can_upload"];

		$title_mess = $textof = '';
		if(isset($_GET['idMessage'])) {
			list($title_mess, $textof) = sql_fetch_row(sql_query("
			SELECT title, textof
			FROM ".$this->prefix."_sysforum
			WHERE idMessage = '".(int)$_GET['idMessage']."'"));

			$textof = preg_replace('/<br />/' ,'><br />' , $textof );
		}

		//if($erased_t && !$mod_perm) {
		if(!$write_perm && !$mod_perm) {

			$res.=$this->lang->def("_CANNOTENTER");
			return $res;
		}

		require_once(_base_."/lib/lib.form.php");
		$form=new Form();

		$form_url=$url."&amp;sf_op=insmessage".$this->getUrlExtra();
		$res.=$form->openForm("comment_form", $form_url, "", "", "multipart/form-data");

		$res.=$form->openElementSpace();

		$res.=$form->getTextfield($this->lang->def("_SUBJECT").":", "title", "title", 255);
		$res.=$form->getTextarea($this->lang->def("_DESCRIPTION").":", "textof", "textof");

		if($upload_perm) {
			$res.=$form->getFilefield($this->lang->def("_ATTACHMENT").":", "attach", "attach");
		}

		$res.=$form->closeElementSpace();
		$res.=$form->openButtonSpace();
		$res.=$form->getButton('save', 'save', $this->lang->def('_SEND'));
		$res.=$form->getButton('undo', 'undo', $this->lang->def('_UNDO'));
		$res.=$form->closeButtonSpace();
		$res.=$form->closeForm();

		return $res;
	}



	// XXX: insmessage
	function insmessage($tk, $perm, $url) {
		global $activeForumUpload;

		$mod_perm=$perm["can_moderate"];
		$read_perm=$perm["can_read"];
		$write_perm=$perm["can_write"];
		$upload_perm=$perm["can_upload"];

		/* $user_grp=getUserGroup(false, true);
		$in_grp=implode(",", $user_grp); */

		if (!$write_perm && !$mod_perm) die("You can't access!");

		/*if(!$mod_perm) {
			$query_view_forum = "
			SELECT DISTINCT f.idForum, f.title, f.locked
			FROM ( ".$prefixCms."_forum AS f LEFT JOIN ".$prefixCms."_forum_access AS fa
					ON ( f.idForum = fa.idForum ) )
					LEFT JOIN ".$prefixCms."_groupuser AS gu
						ON ( fa.idGroup = gu.idGroup )
			WHERE ( fa.idGroup IN ($in_grp) AND fa.can_read='1' ) AND f.idForum = '".$idF."'";

			$re_forum = sql_query($query_view_forum);
			if(!sql_num_rows($re_forum)) {
				errorCommunication(_ISLOCKED);
				return;
			}
			list( $idF, $title, $locked_f ) = sql_fetch_row($re_forum);
		}
		else {
			list( $idF, $title, $locked_f ) = sql_fetch_row(sql_query("
			SELECT idForum, title, locked
			FROM ".$prefixCms."_forum
			WHERE idForum = '".(int)$idF."'"));
		}*/

		//if(!$idF) return;
		if( ($locked_t || $erased_t || $locked_f) && !$mod_perm ) {
			errorCommunication(_ISLOCKED);
			return;
		}

		$url.=(preg_match("/\?/", $url) ? "&" : "?")."sf_event=add".$this->getUrlExtra();
		$back=str_replace("&amp;", "&", $url);

		$check_txt=trim(strip_tags($_POST['textof']));
		if (empty($check_txt)) {
			Util::jump_to($back, $this->getAnchor());
			return 0;
		}

		if($_POST['title'] == '') $_POST['title'] = $this->lang->def("_NOTITLE");
		//save attachment
		$name_file = '';
		if( ($_FILES['attach']['name'] != '') && ( $upload_perm) ) {
			$name_file = $this->save_file( $_FILES['attach']);
		}

		//if ((int)$_SESSION['sesCmsUser'] > 1) $author=(int)$_SESSION['sesCmsUser']; else $author=1;
		$author=Docebo::user()->getIdSt();
		$ins_mess_query = "
		INSERT INTO ".$this->prefix."_sysforum
		SET ".get_sql_tk_str($tk, "",", ").",
			title = '".$_POST['title']."',
			textof = '".$_POST['textof']."',
			author = '".$author."',
			posted = NOW(),
			attach = '$name_file'";

		if(!sql_query( $ins_mess_query )) {
			//errorCommunication(_ERRINSFORUM);
			//-TP// delete_file( $name_file );
			return 0;
		}


		Util::jump_to($back, $this->getAnchor());
	}



	// XXX: modmessage
	function modmessage($tk, $perm, $url, $pb) {
		$res="";
		//global $activeForumUpload;

		if ((int)$pb != 0)
			$pbtxt="&amp;pb=$pb"; else $pbtxt="";

		$mod_perm=$perm["can_moderate"];
		$read_perm=$perm["can_read"];
		$write_perm=$perm["can_write"];
		$upload_perm=$perm["can_upload"];

		$title_mess = $textof = '';
		if(isset($_GET['idMessage'])) {
			list($title_mess, $textof, $author) = sql_fetch_row(sql_query("
			SELECT title, textof, author
			FROM ".$this->prefix."_sysforum
			WHERE idMessage = '".(int)$_GET['idMessage']."'"));
		}
		else return;

		if( $author != Docebo::user()->getIdSt() ) return;


		//if( ($erased_t || $locked_t) && !$mod_perm) {
		if(!$write_perm && !$mod_perm) {
			$res.='<div class="stdBlock">'.$this->lang->def("_CANNOTENTER").'</div>';
			return $res;
		}



		require_once(_base_."/lib/lib.form.php");
		$form=new Form();

		$form_url=$url."&amp;sf_op=upmessage".$this->getUrlExtra();
		$res.=$form->openForm("comment_form", $form_url, "", "", "multipart/form-data");

		$res.=$form->openElementSpace();

		$res.=$form->getTextfield($this->lang->def("_SUBJECT").":", "title", "title", 255, $title_mess);
		$res.=$form->getTextarea($this->lang->def("_DESCRIPTION").":", "textof", "textof", $textof);

		if($upload_perm) {
			$res.=$form->getFilefield($this->lang->def("_ATTACHMENT").":", "attach", "attach");
		}

		$res.=$form->getHidden("idMessage", "idMessage", (int)$_GET['idMessage']);

		$res.=$form->closeElementSpace();
		$res.=$form->openButtonSpace();
		$res.=$form->getButton('save', 'save', $this->lang->def('_SAVE'));
		$res.=$form->getButton('undo', 'undo', $this->lang->def('_UNDO'));
		$res.=$form->closeButtonSpace();
		$res.=$form->closeForm();

		return $res;
	}

	// XXX: upmessage
	function upmessage($tk, $perm, $url, $pb) {
		$res="";
		global $activeForumUpload;

		if ((int)$pb != 0) $pbtxt="&amp;pb=$pb"; else $pbtxt="";

		list($author, $old_file) = sql_fetch_row(sql_query("
		SELECT author, attach
		FROM ".$this->prefix."_sysforum
		WHERE idMessage = '".(int)$_POST['idMessage']."'"));

		$mod_perm=$perm["can_moderate"];
		$read_perm=$perm["can_read"];
		$write_perm=$perm["can_write"];
		$upload_perm=$perm["can_upload"];

		if (!$write_perm && !$mod_perm) die("You can't access!");

		if( $author != Docebo::user()->getIdSt() ) return;

		//if( ($erased_t || $locked_t) && !$mod_perm) {
		if(!$write_perm && !$mod_perm) {
			$res.=$this->lang->def("_CANNOTENTER");
			return $res;
		}

		$url.=(preg_match("/\?/", $url) ? "&" : "?")."sf_event=modmessage".$this->getUrlExtra();
		$back=str_replace("&amp;", "&", $url);

		$check_txt=trim(strip_tags($_POST['textof']));
		if (empty($check_txt)) {
			Util::jump_to($back, $this->getAnchor());
			return 0;
		}

		if($_POST['title'] == '') $_POST['title'] = $this->lang->def("_NOTITLE");

		//save attachment
		$name_file = '';
		if( ($_FILES['attach']['name'] != '') && ( $upload_perm) ) {
			$name_file = $this->save_file( $_FILES['attach']);

			if (!empty($old_file))
				$this->delete_file($old_file);
		}

		$ins_mess_query = "
		UPDATE ".$this->prefix."_sysforum
		SET title = '".$_POST['title']."',
			textof = '".$_POST['textof']."'
			".( $name_file != "" ? ",attach = '".addslashes($name_file)."'" : '' )."
		WHERE idMessage = '".(int)$_POST['idMessage']."' AND
			author = '".(int)Docebo::user()->getIdSt()."'";

		if(!sql_query( $ins_mess_query )) {
			errorCommunication($this->lang->def("_ERRINSFORUM"));
			$this->delete_file( $name_file );
			return;
		}

		Util::jump_to($back, $this->getAnchor());
	}


	// XXX: lockmessage

	function lockmessage($tk, $perm, $url, $pb) {

		$mod_perm=$perm["can_moderate"];
		$read_perm=$perm["can_read"];
		$write_perm=$perm["can_write"];
		$upload_perm=$perm["can_upload"];

		list( $lock ) = sql_fetch_row(sql_query("
		SELECT locked
		FROM ".$this->prefix."_sysforum
		WHERE idMessage = '".(int)$_GET['idMessage']."'"));

		if($lock == 1) $new_status = 0;
		else $new_status = 1;

		sql_query("
		UPDATE ".$this->prefix."_sysforum
		SET locked = '$new_status'
		WHERE idMessage = '".(int)$_GET['idMessage']."'");

		$back=str_replace("&amp;", "&", $url);
		header("location: $back");
	}


	function save_file( $file ) {

		$path='/common/comment/';

		require_once( _base_.'/lib/lib.upload.php' );

		if($file['name'] != '') {
			$savefile = rand(0,100)._.time()._.$file['name'];
			if(!file_exists ($path.$savefile)) {
				sl_open_fileoperations();
				if(!sl_upload($file['tmp_name'], $path.$savefile)){
					sl_close_fileoperations();
					// errorCommunication(_ERROR_UPLOAD);
					return '';
				}
				sl_close_fileoperations();
				return $savefile;
			}
			else {
				//  errorCommunication(_ERROR_UPLOAD);
				return '';
			}
		}
		else return '';
	}

	function delete_file( $name ) {

		$path='/common/comment/';

		require_once( _base_.'/lib/lib.upload.php' );

		if($name != '')
			return sl_unlink( $path.$name );
	}


	function download_file() {

			$path='/common/comment/';

			require_once(_base_.'/lib/lib.download.php');

			//find file
			list($title, $attach) = sql_fetch_row(sql_query("
			SELECT title, attach
			FROM ".$this->prefix."_sysforum
			WHERE idMessage='".(int)$_GET['sf_fileid']."'"));
			if(!$attach) {
				echo '<div class="errorBlock">Sorry, such file does not exist!</div>';
				return;
			}
			//recognize mime type
			$expFileName = explode('.', $attach);
			$ext = $expFileName[count($expFileName) - 1];
			array_pop($expFileName);

			$attach_no_ext = implode('', $expFileName);
			$break_apart = explode('_', $attach_no_ext);
			$break_apart[0] = $break_apart[1] = '';
			$sendname = implode('', $break_apart);
			if($sendname == '') $sendname = $attach_no_ext;

			//send file
			sendFile($path, $attach, $ext, $sendname);
	}



}





// --------------------------------------------------------------------------------------------------------------------
// Other functions used by the class


function get_sql_tk_str($tk, $table="", $sep=" AND ") {

	if ($table != "")
		$table.=".";

	$where_arr=array();

	$where_arr[]=$table."key1='".$tk[1]."'";

	if ((int)$tk[2] > 0) {
		$where_arr[]=$table."key2='".$tk[2]."'";
	}

	if (($tk[3] != NULL) && ((int)$tk[3] > 0)) {
		$where_arr[]=$table."key3='".$tk[3]."'";
	}

	return implode($sep, $where_arr);
}



// --------------------------------------------------------------------------------------------------------------------
// EOF.
?>
