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

class Learning_Poll extends Learning_Object {
	
	var $id;
	
	var $idAuthor;
	
	var $title;
	
	var $back_url;


	var $db;

	/**
	 * function learning_Test()
	 * class constructor
	 **/
	function Learning_Poll( $id = NULL ) {
		parent::Learning_Object( $id );
		if( $id !== NULL ) {
			$res = $this->db->query("SELECT author, title FROM %lms_poll WHERE id_poll = '".(int)$id."'");
			if ($res && $this->db->num_rows($res)>0) {
				list( $this->idAuthor, $this->title ) = $this->db->fetch_row($res);
				$this->isPhysicalObject = true;
			}
		}
	}
	
	function getObjectType() {
		return 'poll';
	}
	
	function getParamInfo() {
		
        $params = parent::getParamInfo();        
        return $params;
	}
	
	function renderCustomSettings( $arrParams, $form, $lang ) {

        $out = parent::renderCustomSettings($arrParams, $form, $lang);
		return $out;
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
		
		require_once( $GLOBALS['where_lms'].'/modules/poll/poll.php' );
		addpoll( $this );
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
		
		require_once( $GLOBALS['where_lms'].'/modules/poll/poll.php' );
		modpollgui( $this );
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
		
		//finding quest
		$reQuest = sql_query("
		SELECT q.id_quest, q.type_quest, t.type_file, t.type_class 
		FROM ".$GLOBALS['prefix_lms']."_pollquest AS q JOIN ".$GLOBALS['prefix_lms']."_quest_type_poll AS t 
		WHERE q.id_poll = '".$id."' AND q.type_quest = t.type_quest");
		if(!sql_num_rows($reQuest)) return true;
		//deleting answer
		while( list($id_quest, $type_quest, $type_file, $type_class) = sql_fetch_row($reQuest) ) {
			
			require_once($GLOBALS['where_lms'].'/modules/question_poll/'.$type_file);
			
			$quest_obj = eval("return new $type_class( $id_quest );");
			if(!$quest_obj->del())  {
				$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE');
				return false;
			}
	
		}
		if( !sql_query("DELETE FROM ".$GLOBALS['prefix_lms']."_polltrack WHERE id_poll = '".$id."'") ) {
			$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE');
			return false;
		}
		if( !sql_query("DELETE FROM ".$GLOBALS['prefix_lms']."_pollquest WHERE id_poll = '".$id."'") ) {
			$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE');
			return false;
		}
		if( !sql_query("DELETE FROM ".$GLOBALS['prefix_lms']."_poll WHERE id_poll = '".$id."'") ) {
			$_SESSION['last_error'] = Lang::t('_OPERATION_FAILUREPOLL');
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
		$poll_info = sql_fetch_array(sql_query("
		SELECT author, title, description
		FROM ".$GLOBALS['prefix_lms']."_poll 
		WHERE id_poll = '".(int)$id."'"));
		
		//insert new item
		$ins_query = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_poll
		SET author = '".(int)$poll_info['author']."', 
			title = '".sql_escape_string($poll_info['title'])."', 
			description = '".sql_escape_string($poll_info['description'])."'";
		if(!sql_query($ins_query)) return false;
		list($id_new_poll) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		if(!$id_new_poll) return false;
		
		//finding quest
		$reQuest = sql_query("
		SELECT q.id_quest, q.type_quest, t.type_file, t.type_class 
		FROM ".$GLOBALS['prefix_lms']."_pollquest AS q JOIN ".$GLOBALS['prefix_lms']."_quest_type_poll AS t 
		WHERE q.id_poll = '".$id."' AND q.type_quest = t.type_quest");
		//retriving quest
		while( list($id_quest, $type_quest, $type_file, $type_class) = sql_fetch_row($reQuest) ) {
			
			require_once($GLOBALS['where_lms'].'/modules/question_poll/'.$type_file);
			$quest_obj = eval("return new $type_class( $id_quest );");
			$new_id = $quest_obj->copy($id_new_poll);
			if(!$new_id) {
				$this->del( $id_new_poll );
				
				$_SESSION['last_error'] = Lang::t('_POLL_ERR_COPY_QUEST').' : '.$type_class.'( '.$id_quest.' )';
				return false;
			}
		}
		return $id_new_poll;
	}
	
	/**
	 * function play( $id, $id_param, $back_url )
	 * @param int $id contains the resource id
	 * @param int $id_param contains the id needed for params retriving
	 * @param string $back_url contain the back url
	 * @return nothing return
	 **/
	function play( $id, $id_param, $back_url ) {
		require_once( $GLOBALS['where_lms'].'/modules/poll/do.poll.php' );
		
		$this->id = $id;
		$this->back_url = $back_url;
		
		$step = importVar('next_step');
		switch($step) {
			case "poll_review" : {
				review($this, $id_param);
			};break;
			case "play" : {
				playPollDispatch($this, $id_param);
			};break;
			default : {
				intro($this, $id_param);
			};break;
		}
	}



	/**
	 * function search( $key )
	 * @param string $key contains the keyword to search
	 * @return array with results found
	 **/
	function search( $key ) {
		$output = false;
		$query = "SELECT * FROM %lms_poll WHERE title LIKE '%".$key."%' OR description LIKE '%".$key."%' ORDER BY title";
		$res = $this->db->query($query);
		$results = array();
		if ($res) {
			$output = array();
			while ($row = $this->db->fetch_obj($res)) {
				$output[] = array(
					'id' => $row->id_poll,
					'title' => $row->title,
					'description' => $row->description
				);
			}
		}
		return $output;
	}

    public function canBeCategorized() {
        return false;
    }

}

?>
