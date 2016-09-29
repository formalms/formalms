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

require_once( dirname( __FILE__ ).'/learning.object.php' );

class Learning_Htmlpage extends Learning_Object {
	
	var $id;
	
	var $idAuthor;
	
	var $title;
	
	var $back_url;
	
	/**
	 * function learning_Object()
	 * class constructor
	 **/
	function Learning_Htmlpage( $id = NULL ) {
		
		parent::Learning_Object( $id );
		if( $id !== NULL ) {
			$res = $this->db->query("SELECT author, title FROM %lms_htmlpage WHERE idPage = '".$id."'");
			if ($res && $this->db->num_rows($res)>0) {
				list( $this->idAuthor, $this->title ) = $this->db->fetch_row($res);
				$this->isPhysicalObject = true;
			}
		}
	}
	
	function getObjectType() {
		return 'htmlpage';
	}
	
	/**
	 * function create( $back_url )
	 * @param string $back_url contains the back url
	 * @return nothing
	 * attach the id of the created object at the end of back_url with the name, in attach the result in create_result
	 *
	 * static
	 **/
	function create( $back_url ) {
		
		$this->back_url = $back_url;
		
		unset($_SESSION['last_error']);
		
		require_once( $GLOBALS['where_lms'].'/modules/htmlpage/htmlpage.php' );
		addpage( $this );
	}
	
	/**
	 * function edit
	 * @param int $id contains the resource id
	 * @param string $back_url contains the back url
	 * @return nothing
	 * attach in back_url id_lo that is passed $id and attach edit_result with the result of operation in boolean format 
	 **/
	function edit( $id, $back_url ) {
		
		$this->id = $id;
		$this->back_url = $back_url;
		
		unset($_SESSION['last_error']);
		
		require_once( $GLOBALS['where_lms'].'/modules/htmlpage/htmlpage.php' );
		modpage( $this );
	}
	
	/**
	 * function del
	 * @param int $id contains the resource id
	 * @param string $back_url contains the back url (not used yet)
	 * @return false if fail, else return the id lo
	 **/
	function del( $id, $back_url = NULL ) {
		checkPerm('view', false, 'storage');
		
		unset($_SESSION['last_error']);
		
		$delete_query = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_htmlpage 
		WHERE idPage = '".$id."'";
		if(!sql_query( $delete_query )) {
			$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE', 'htmlpage');
			return false;
		}
		$delete_query = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_htmlpage_attachment
		WHERE idpage = '".$id."'";
		if(!sql_query( $delete_query )) {
			$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE', 'htmlpage');
			return false;
		}	
		return $id;
	}
	
	/**
	 * function copy( $id, $back_url )
	 * @param int $id contains the resource id
	 * @param string $back_url contain the back url (not used yet)
	 * @return int $id if success FALSE if fail
	 **/
	function copy( $id, $back_url = NULL ) {
		
		//find source info
		list($title, $textof, $author) = sql_fetch_row(sql_query("
		SELECT title, textof, author 
		FROM ".$GLOBALS['prefix_lms']."_htmlpage 
		WHERE idPage = '".(int)$id."'"));
		
		//insert new item
		$insertQuery = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_htmlpage 
		SET title = '".sql_escape_string($title)."',
			textof = '".sql_escape_string($textof)."',
			author = '".$author."'";
		
		if(!sql_query($insertQuery)) {
			return false;
		}
		list($idPage) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		return $idPage;
	}
	
	/**
	 * function play( $id, $id_param, $back_url )
	 * @param int $id contains the resource id
	 * @param int $id_param contains the id needed for params retriving
	 * @param string $back_url contain the back url
	 * @return nothing return
	 **/
	function play( $id, $id_param, $back_url ) {
		//-kb-play-// if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
		
		$this->id = $id;
		$this->back_url = $back_url;
		
		$this->checkObjPerm();

		list($title, $textof) = sql_fetch_row(sql_query("
		SELECT title, textof 
		FROM ".$GLOBALS['prefix_lms']."_htmlpage 
		WHERE idPage = '".(int)$id."'"));
		
		// recuper gli allegati 
		$path = '/appLms/htmlpages/';
		$query = "SELECT * FROM ".$GLOBALS['prefix_lms']."_htmlpage_attachment WHERE idpage = ".$id;
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
			
		require_once( $GLOBALS['where_lms'].'/lib/lib.param.php' );
		$idReference = getLOParam($id_param, 'idReference');
		// NOTE: Track only if $idReference is present 
		if( $idReference !== FALSE ) {
			require_once( $GLOBALS['where_lms'].'/class.module/track.htmlpage.php' );
			list( $exist, $idTrack) = Track_Htmlpage::getIdTrack($idReference, getLogUserId(), $this->id, TRUE );
			if( $exist ) {
				$ti = new Track_Htmlpage( $idTrack );
				$ti->setDate(date('Y-m-d H:i:s'));
				$ti->status = 'completed';
				$ti->update();
			} else {
				
				$ti = new Track_Htmlpage( false );
				$ti->createTrack( $idReference, $idTrack, getLogUserId(), date('Y-m-d H:i:s'), 'completed', 'htmlpage' );
			}
		}
		
		$atts = '';
		foreach($attachments as $attachment) {
			$atts .= "<a id=\"".$attachment['id']."\" href=\"".$attachment['file']."\" target=\"_blank\">".$attachment['title']."</a><br/>";
		}
		
		$GLOBALS['page']->add('<div id="top" class="std_block">'
			.getBackUi( str_replace( '&', '&amp;', $this->back_url ), Lang::t('_BACK'))
			.'<div class="title">'.$title.'</div>'
			.'<div class="textof">'.$textof, 'content');

		if ($atts != '')
			$GLOBALS['page']->add('<br/><br/></div><div class="attach">'.Lang::t('_ATTACH_TITLE', 'htmlpage', 'lms').'<br/>'.$atts.'</div>', 'content');
		else
			$GLOBALS['page']->add('</div>', 'content');
			
		$GLOBALS['page']->add('<br /><br />'
			/*.'<a href="#top" title="'. Lang::t('_BACKTOTOP', 'htmlpage', 'lms').'">'
				.'<img src="'.getPathImage().'standard/upcheck.gif" alt="'. Lang::t('_BACKTOTOP', 'htmlpage', 'lms').'" />'
				. Lang::t('_BACKTOTOP', 'htmlpage', 'lms').'</a>'*/
			.getBackUi( str_replace( '&', '&amp;', $this->back_url ), Lang::t('_BACK'))
			.'</div>', 'content');
	}
	
	/**
	 * function import( $source, $back_url ) NOT IMPLEMENTED YET
	 * @param string $source contains the filename 
	 * @return bool TRUE if success FALSE if fail
	 * if operation success attach the new id at the back url with the name id_lo 
	 **/
	function import( $source, $back_url ) {
	
	}
	
	/**
	 * function export( $id, $format, $back_url ) NOT IMPLEMENTED YET
	 * @param string $id contain resource id
	 * @param string $format contain output format
	 * @param string $back_url contain the back url 
	 * @return bool TRUE if success FALSE if fail
	 **/
	function export( $id, $format, $back_url ) {
		
	}


	/**
	 * function search( $key )
	 * @param string $key contains the keyword to search
	 * @return array with results found
	 **/
	function search( $key ) {
		$output = false;
		$query = "SELECT * FROM %lms_htmlpage WHERE title LIKE '%".$key."%' ORDER BY title";
		$res = $this->db->query($query);
		$results = array();
		if ($res) {
			$output = array();
			while ($row = $this->db->fetch_obj($res)) {
				$output[] = array(
					'id' => $row->idPage,
					'title' => $row->title,
					'description' => ''
				);
			}
		}
		return $output;
	}

}

?>
