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
 * @version  $Id:$
 */

define("AJCOMM_ID", 		0);
define("AJCOMM_RESTYPE", 	1);
define("AJCOMM_EXTKEY", 	2);
define("AJCOMM_AUTHOR", 	3);
define("AJCOMM_POSTED", 	4);
define("AJCOMM_TEXTOF", 	5);
define("AJCOMM_TREE", 		6);
define("AJCOMM_PARENT", 	7);
define("AJCOMM_MODERATED", 	8);

class AjaxComment {
	
	var $colums = array(
		AJCOMM_ID 			=> 'id_comment',
		AJCOMM_RESTYPE 		=> 'resource_type',
		AJCOMM_EXTKEY 		=> 'external_key',
		AJCOMM_AUTHOR		=> 'id_author',
		AJCOMM_POSTED 		=> 'posted_on',
		AJCOMM_TEXTOF 		=> 'textof',
		AJCOMM_TREE 		=> 'history_tree',
		AJCOMM_PARENT 		=> 'id_parent',
		AJCOMM_MODERATED 	=> 'moderated'
	);
	
	var $resource_type;
	
	var $resource_platform;
	
	var $reply_status;
	
	var $_comment_table;
	
	var $_order_post = true;
	
	function AjaxComment($resource_type, $resource_platform) {
		
		ksort($this->colums);
		reset($this->colums);
		
		$this->resource_type 		= $resource_type;
		$this->resource_platform 	= $resource_platform;
		
		if($resource_platform == 'framework') $resource_platform = 'fw';
		
		$this->_comment_table 		= $GLOBALS['prefix_'.$resource_platform].'_comment_ajax';
		
		if(!isset($GLOBALS['current_user'])) $this->reply_status = false;
		else $this->reply_status = !Docebo::user()->isAnonymous();
	}
	
	// some abstraction function
	function _query($query) { $re = sql_query($query); return $re; }
	function _fetch_row($resource) { return sql_fetch_row($resource); }
	function _fetch_assoc($resource) { return sql_fetch_assoc($resource); }
	function _num_rows($resource) { return sql_num_rows($resource); }
	
	function setTable($table) {
		
		$this->_comment_table = $table;
	}
	
	function isReplyActive() {
		
		return $this->reply_status;
	}
	
	function canReply($status) {
		
		$this->reply_status = ( $status ? true : false );	
	}
	
	/**
	 * return the list of all the data about the comments
	 */
	function getCommentByResourceKey($ext_key, $from = false, $for = false) {
		
		$comments = array();
		$query = "
		SELECT ".implode(', ', $this->colums)."
		FROM ".$this->_comment_table."
		WHERE ".$this->colums[AJCOMM_RESTYPE]." = '".$this->resource_type."' 
			AND ".$this->colums[AJCOMM_EXTKEY]." = '".$ext_key."' 
		ORDER BY ".( $this->_order_post ? $this->colums[AJCOMM_POSTED] : $this->colums[AJCOMM_TREE] );
		if($from !== false && $for !== false) $query .= " LIMIT ".$from.", ".$for;
		
		$re_comments = $this->_query($query);
		while($data = $this->_fetch_row($re_comments)) {
			
			$comments[$data[AJCOMM_ID]] = $data;
		}
		return $comments;
	}
	
	/**
	 * return a list with the number f comment for a specific istance of a resource
	 * @return int the number of comment
	 */
	function getCommentCountForResourceKey($ext_key) {
		
		$comments = array();
		$query = "
		SELECT COUNT(*)
		FROM ".$this->_comment_table."
		WHERE ".$this->colums[AJCOMM_RESTYPE]." = '".$this->resource_type."' 
			AND ".$this->colums[AJCOMM_EXTKEY]." = '".$ext_key."'";
		
		if(!$re_comments = $this->_query($query)) return 0;
		$data = $this->_fetch_row($re_comments);
		return $data[0];
	}
	
	/**
	 * return a list with the number of comment for a resource
	 * @return array a list of all the resource instance and the number of comments
	 */
	function getResourceCommentCount() {
		
		$comments = array();
		$query = "
		SELECT ".$this->colums[AJCOMM_EXTKEY].", COUNT(*)
		FROM ".$this->_comment_table."
		WHERE ".$this->colums[AJCOMM_RESTYPE]." = '".$this->resource_type."' 
		GROUP BY ".$this->colums[AJCOMM_EXTKEY];
		
		$re_comments = $this->_query($query);
		while($data = $this->_fetch_row($re_comments)) {
			
			$comments[$data[0]] = $data[1];
		}
		return $comments;
	}
	/**
	 * moderate a messagge
	 * @param int $comment_id the id of the comment
	 * 
	 * @return bool true if the comments  correctly false in case of trouble 
	 */
	function moderateComment($comment_id) {
		
		$query = "
		UPDATE ".$this->_comment_table."
		SET ".$this->colums[AJCOMM_MODERATED]." = 1
		WHERE ".$this->colums[AJCOMM_ID]." = '".$comment_id."'";
		return $this->_query($query);
	}	
	
	/**
	 * de-moderate a messagge
	 * @param int $comment_id the id of the comment
	 * 
	 * @return bool true if the comments  correctly false in case of trouble 
	 */
	function demoderateComment($comment_id) {
		
		$query = "
		UPDATE ".$this->_comment_table."
		SET ".$this->colums[AJCOMM_MODERATED]." = 0
		WHERE ".$this->colums[AJCOMM_ID]." = '".$comment_id."'";
		return $this->_query($query);
	}	
	
	/**
	 * insert a new a comment 
	 * @param 	array 	$comment_data 	the list of all the data to insert, use the constans AJCOMM_* as array keys 
	 * 									(do not pass AJCOMM_ID, and AJCOMM_RESTYPE is optional)
	 * 
	 * @return bool true if the comments deleted correctly false in case of trouble 
	 */
	function addComment($comment_data) {
		
		if($comment_data[AJCOMM_TEXTOF] == '') return false;
		$query = "
		INSERT INTO ".$this->_comment_table."
		( 	".$this->colums[AJCOMM_ID].", ".$this->colums[AJCOMM_RESTYPE].", ".$this->colums[AJCOMM_EXTKEY].", ".$this->colums[AJCOMM_AUTHOR].", 
			".$this->colums[AJCOMM_POSTED].", ".$this->colums[AJCOMM_TEXTOF].", ".$this->colums[AJCOMM_TREE].", ".$this->colums[AJCOMM_PARENT].", 
			".$this->colums[AJCOMM_MODERATED]." 
		) VALUES (
			NULL,
			'".( isset($comment_data[AJCOMM_RESTYPE]) ? $comment_data[AJCOMM_RESTYPE] : $this->resource_type )."',
			'".$comment_data[AJCOMM_EXTKEY]."', 
			".(int)$comment_data[AJCOMM_AUTHOR].", 
			'".$comment_data[AJCOMM_POSTED]."', 
			'".$comment_data[AJCOMM_TEXTOF]."', 
			'".$comment_data[AJCOMM_TREE]."', 
			".(int)$comment_data[AJCOMM_PARENT].", 
			".(int)$comment_data[AJCOMM_MODERATED]."
		)";
		return $this->_query($query);
	}
	
	/**
	 * update a comment 
	 * @param 	array 	$comment_key 	the list of all the value to use as key, use the constans AJCOMM_* as array keys
	 * @param 	array 	$comment_data 	the list of all the data to update, use the constans AJCOMM_* as array keys
	 * 
	 * @return bool true if the comments deleted correctly false in case of trouble 
	 */
	function updateComment($comment_key, $comment_data) {
		
		$update_key 	= array();
		$update_data 	= array();
		foreach($comment_key as $key => $value) {
			$update_key[] = $this->colums[$key]." = '".$value."'";
		}
		foreach($comment_key as $key => $value) {
			$update_data[] = $this->colums[$key]." = '".$value."'";
		}
		
		if(empty($update_key) || empty($update_data)) return true;
		
		$query = "
		UPDATE ".$this->_comment_table."
		SET ".implode(',', $update_data)."
		WHERE ".implode(' AND ', $update_key)." ";
		return $this->_query($query);
	}
	
	/**
	 * delete a specific comment
	 * @param int $comment_id the id of the comment
	 * 
	 * @return bool true if the comment deleted correctly false in case of trouble 
	 */
	function deleteComment($comment_id) {
		
		$query = "
		DELETE FROM ".$this->_comment_table."
		WHERE ".$this->colums[AJCOMM_ID]." = '".$comment_id."'";
		
		return $this->_query($query);
	}
	
	/**
	 * delete all the comment of a specifi resource
	 * @param mixed $ext_key the external key of the comments
	 * 
	 * @return bool true if the comments deleted correctly false in case of trouble 
	 */
	function deleteCommentByResourceKey($ext_key) {
		
		$query = "
		DELETE FROM ".$this->_comment_table."
		WHERE ".$this->colums[AJCOMM_RESTYPE]." = '".$this->resource_type."' 
			AND ".$this->colums[AJCOMM_EXTKEY]." = '".$ext_key."' ";
		
		return $this->_query($query);
	}
	
}

class AjaxCommentRender {
	
	var $_platform;
	
	var $_module;
	
	var $_with_action = false;
	
	var $_with_reply = false;
	
	var $_acl_man = false;
	
	var $_authors = false;
	
	var $_data = false;
	
	var $_continue = true;
	
	var $_profile = false;	

	function AjaxCommentRender($module, $platform) {
		
		$this->_platform 	= $platform;
		$this->_module 		= $module;
	}
	
	function setCommentToDisplay($data) {
		
		require_once(_base_.'/lib/lib.user_profile.php');
	
		$users = array();
		$this->_data = $data;
		while(list($id, $comment) = each($this->_data)) { $users[] = $comment[AJCOMM_AUTHOR]; }
		reset($this->_data);
		
		$this->_acl_man = Docebo::user()->getAclManager();
		$this->_authors = $this->_acl_man->getUsers($users);
		
		
		$this->profile = new UserProfile( 0 );
		$this->profile->init('profile', 'framework', '', 'ap');
	}
	
	function nextComment() {
		
		if(!$this->_continue) return false;
		$this->_continue = list( ,$comment_data) = each($this->_data);
		if(!$this->_continue) return false;
		
		$this->profile->setIdUser($comment_data[AJCOMM_AUTHOR]);
		$this->profile->manualLoadUserData($this->_authors[$comment_data[AJCOMM_AUTHOR]]);
		
		$html = '<div class="ajcom_comment">';
		
		$html .= '<h2>'
			.$this->profile->getUserPhotoOrAvatar('micro').' '.$this->profile->resolveUsername()
			.' <span class="ajcom_date">'.createDateDistance($comment_data[AJCOMM_POSTED], $this->_module, true).'</span></h2>';
		
		$html .= '<p id="comment_'.$comment_data[AJCOMM_ID].'" class="ajcom_textof">'.$comment_data[AJCOMM_TEXTOF].'</p>';
		
		if($this->_with_reply) {
		
		}
		
		if (Docebo::user()->getUserLevelId()=="/framework/level/godadmin") {
			$html .= '<p><a href="javascript:;" onclick="delComment('.$comment_data[AJCOMM_ID].','.$comment_data[AJCOMM_EXTKEY].')">'
				.'<img src="'.getPathImage().'standard/delete.png" alt="'. Lang::t('_DEL', 'standard', 'framework').'" />'
				.'</a></p>';
		}
		$html .= '</div>';
		
		return $html;
			
	}
	
	function isEnd() { return !$this->_continue; }
	
	function getAddCommentMask($ext_key) {
		
		require_once(_base_.'/lib/lib.form.php');
		
		$html = ''
			.Form::openForm('ajax_comment_add' , '', false, false, '', ' onSubmit="addajaxcomment(); return false;"')
			.Form::getHidden( 'ajaxcomment_ext_key', 'ajaxcomment_ext_key', $ext_key)
			.Form::getHidden( 'ajaxcomment_reply_to', 'ajaxcomment_reply_to', '0')
			.'<p>'
			.Form::getLabel('ajaxcomment_textof', Lang::t('_COMMENTS', $this->_module, $this->_platform), 'label_bold')
			
			.'</p>'
			.'<p>'
			.Form::getInputTextarea('ajaxcomment_textof',
									'ajaxcomment_textof', 
									'' )
			.'</p>'
			.'<p>'
			.Form::getButton(	'ajaxcomment_send',
								'ajaxcomment_send', 
							 Lang::t('_SEND', $this->_module, $this->_platform),
								'' )
			.'</p>'
			
			.Form::closeForm();
		return $html;
	}
	
	function getAddCommentMask_2($ext_key) {
		require_once(_base_.'/lib/lib.form.php');
		return ""
			
			.Form::openForm('ajax_comment_add' , '', 'align_center', false, false, ' onsubmit="return false;"')
			
			//."<div style=\"text-align:center;\">"
			."<input id=\"ajaxcomment_ext_key\" name=\"ajaxcomment_ext_key\" value=\"$ext_key\" type=\"hidden\">"
			."<input id=\"ajaxcomment_reply_to\" name=\"ajaxcomment_reply_to\" value=\"0\" type=\"hidden\">"
			."<p>"
			."<label class=\"label_bold\" for=\"ajaxcomment_textof\">". Lang::t('_COMMENTS', $this->_module, $this->_platform)."</label>"
			."</p>"
			."<p>"
			."<textarea class=\"textarea\" id=\"ajaxcomment_textof\" name=\"ajaxcomment_textof\" rows=\"5\" cols=\"40\">"
			."</textarea></p><p><button onclick=\"addajaxcomment();\">". Lang::t('_SEND', $this->_module, $this->_platform)."</button>"
			."</p>"
			//."</div>"
			
			.Form::closeForm()
			."";
		
	}
}

?>