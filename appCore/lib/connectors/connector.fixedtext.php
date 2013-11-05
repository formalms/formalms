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
 * @package admin-core
 * @subpackage io-operation
 */
 
require_once( dirname(__FILE__) . '/lib.connector.php' );

/** 
 * class for define csv connection to data source.
 * @package		Docebo
 * @subpackage	Export
 * @version 	1.0
 * @author		Marco Valloni <marco (@) docebo (.) com>
 **/
class DoceboConnectorFixedText extends DoceboConnector {
  	var $curr_file = '';
	var $filename = '';
	var $filehandle = NULL;
	var $param_string = '';
	var $asprintf_string = '';
	var $cols_descriptor = NULL;
	var $row_index = 0;
	var $readwrite = 2; // read = 1, write = 2, readwrite = 3
	var $last_error = "";
	var $name = "";
	var $description = "";
	
	var $last_execution = false;
	
	/**
	 * This constructor require the source file name
	 * @param array $params the array of params
	 *		- 'filename' => name of the file (required)
	 *		- 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
	 *		- 'separator' => string a char with the fields separator (Optional, default = ,)
	**/
	function DoceboConnectorFixedText( $params ) {
		$this->set_config( $params );
	}
	
	function get_config() {
		return array( 	'filepattern' => $this->filename,
						'readwrite' => $this->readwrite,
						'name' => $this->name,
						'description' => $this->description,
						'param_string' => $this->param_string,
						'asprintf_string' => $this->asprintf_string,
						'field_def' => $this->cols_descriptor);
	}
	
	function set_config( $params ) {
		if( isset($params['filepattern']) ) 		$this->filename = $params['filepattern'];
		if( isset($params['readwrite']) )			$this->readwrite = $params['readwrite'];
		if( isset($params['name']) )				$this->name = $params['name'];
		if( isset($params['description']) )			$this->description = $params['description'];
		if( isset($params['param_string']) )		$this->param_string = $params['param_string'];
		if( isset($params['asprintf_string']) )		$this->asprintf_string = $params['asprintf_string'];
		if( isset($params['field_def']) )			$this->cols_descriptor = $params['field_def'];
	}

	function get_configUI() {
		return new DoceboConnectorFixedTextUI($this);
	}
	
	/**
	 * execute the connection to source
	**/
	function connect() {
		$this->close();
		
		list($task_name) = each($_POST['action']['run_task']);
		
		$query =	"SELECT `last_execution`"
					." FROM ".$GLOBALS['prefix_fw']."_task"
					." WHERE `name` = '".$task_name."'";
		
		list($last_execution) = sql_fetch_row(sql_query($query));
		
		$this->last_execution = $last_execution;
		
		$string = str_replace('[blank_space],', '', $this->param_string);
		$string = str_replace(',[blank_space]', '', $string);
		
		$string .= ',[id_course],[date_creation]';
		
		$array_cols = explode(',', $string);
		
		$array_to_unset = array();
		
		foreach($array_cols as $key => $value)
		{
			$last = strlen($value);
			if($last > 3)
			{
				$last--;
				if($value{0} !== '[' || $value{$last} !== ']')
					$array_to_unset[] = $key;
			}
			else
				$array_to_unset[] = $key;
		}
		
		reset($array_cols);
		
		foreach($array_to_unset as $unset_key)
			unset($array_cols[$unset_key]);
		
		$array_cols = array_unique($array_cols);
		
		$this->cols_descriptor = $array_cols;
		
		/* search for file with pattern */
		$pat = str_replace(array("*","?"),array(".*",".{1}"),$this->filename);
		$arr_files = preg_ls (DOCEBOIMPORT_BASEDIR, false, '/'.$pat.'/');
		
		$this->curr_file = DOCEBOIMPORT_BASEDIR.$pat;
		//print_r($this->filename);
		/* open file */
		$this->filehandle = @fopen($this->curr_file, 'w');
		
		if( $this->filehandle === FALSE )
		{
			$this->last_error = 'file not opened: '.$this->curr_file;
			return FALSE;
		}
		
		return true;		
	}
	
	/**
	 * execute the close of the connection 
	**/
	function close() {
		if( $this->filehandle !== NULL )
		{
			if(!@fclose( $this->filehandle ))
				return FALSE;
			
			rename($this->curr_file, DOCEBOIMPORT_BASEDIR.date('Ymd').basename($this->curr_file) );
		}
		$this->filehandle = NULL;
		$this->row_index = 0;
		
		return TRUE;	
	}

	/**
	 * Return the type of the connector 
	 **/
	function get_type_name()
	{
		return "fixedtext-connector";
	}	 
	
	/**
	 * Return the description of the connector 
	 **/
	function get_type_description()
	{
		return "connector to fixed text";
	}	 	

	/**
	 * Return the name of the connection
	 **/
	function get_name()
	{
		return $this->name;
	}	 	

	function get_description()
	{
		return $this->description;
	}
	
	function get_tot_cols() { return count($this->cols_descriptor); }

	function get_cols_descripor() {
		foreach( $this->cols_descriptor as $colname ) {
		  	$arr_cols[] = array(	DOCEBOIMPORT_COLNAME => $colname,
									DOCEBOIMPORT_COLMANDATORY => FALSE,
									DOCEBOIMPORT_DATATYPE => 'text',
									DOCEBOIMPORT_DEFAULT => ''
								);
		}
		return $arr_cols;
	}
	//Not used
	function get_first_row()
	{
		return false;
	}
	//Not used
	function get_next_row()
	{
		return false;
	}
	//Not used
	function is_eof()
	{
		return feof($this->filehandle);
	}
	
	function get_row_index()
	{
		return $this->row_index;
	}
	
	function get_tot_mandatory_cols()
	{
		return 0;
	}

	function add_row( $row )
	{
		$array_substitution = array();
		
		$array_param = explode(',', $this->param_string);
		
		if(array_search('[date_subscription]', $array_param) || array_search('[matriculant]', $array_param))
		{
			$query =	"SELECT idUser, date_inscr"
						." FROM ".$GLOBALS['prefix_lms']."_courseuser"
						." WHERE idCourse = '".$row['[id_course]']."'"
						." AND status = '2'"
						.($this->last_execution !== NULL ? " AND date_complete >= '".$this->last_execution."'" : '');
			
			$result = sql_query($query);
			
			if(mysql_num_rows($result))
			{
				$put = '';
				
				while(list($id_user, $date_inscr) = sql_fetch_row($result))
				{
					foreach($array_param as $param)
						switch($param)
						{
							case '[blank_space]':
								$array_substitution[] = '';
							break;
							
							case '[course_code]':
								$array_substitution[] = html_entity_decode($row['[course_code]'], ENT_QUOTES, "UTF-8");
							break;
							
							case '[matriculant]':
								require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
								
								$field_man = new FieldList();
								//TODO: Modificare con il nome del campo della matricola che hanno in piattaforma
								$id_common_field = $field_man->getFieldIdCommonFromTranslation('Matricola');
								
								$array_field = $field_man->getFieldsAndValueFromUser($id_user);
								
								$array_substitution[] = sprintf("%07d", $array_field[$id_common_field][1]);
							break;
							
							case '[date_subscription]':
								if($date_inscr !== '0000-00-00' && $date_inscr !== '')
									$array_substitution[] = $date_inscr;
								else
									$array_substitution[] = $row['[date_creation]'];
							break;
							
							case '[course_creation]':
								if($row['[create_date]'] !== '0000-00-00')
									$array_substitution[] = $row['[create_date]'];
								else
									$array_substitution[] = $row['[course_begin]'];
							break;
							
							default:
								$array_substitution[] = $param;
							break;
						}
					
					$put .= vsprintf($this->asprintf_string, $array_substitution)."\r\n";
				}
				
				if(@fwrite($this->filehandle, $put))
					return true;
				else
					return false;
			}
			
			return true;
		}
		else
		{
			if($row['[create_date]'] < $this->last_execution)
				return true;
			
			foreach($array_param as $param)
				switch($param)
				{
					case '[blank_space]':
						$array_substitution[] = '';
					break;
					
					case '[course_code]':
						$array_substitution[] = html_entity_decode($row['[course_code]'], ENT_QUOTES, "UTF-8");
					break;
					
					case '[course_name]':
						$array_substitution[] = html_entity_decode(trim($row['[course_name]']), ENT_QUOTES, "UTF-8");
					break;
					
					case '[course_descr]':
						$array_substitution[] = html_entity_decode(str_replace(array("\n", "\r"), '', strip_tags(trim($row['[course_descr]']))), ENT_QUOTES, "UTF-8");
					break;
					
					case '[medium_time]':
						$array_substitution[] = sprintf("%04d", $row['[medium_time]']).'00';
					break;
					
					case '[course_begin]':
						$array_substitution[] = $row['[course_begin]'];
					break;
					
					case '[course_end]':
						if($row['[course_end]'] !== '0000-00-00')
							$array_substitution[] = $row['[course_end]'];
						else
							$array_substitution[] = '2999-12-31';
					break;
					
					case '[course_creation]':
						if($row['[create_date]'] !== '0000-00-00')
							$array_substitution[] = $row['[create_date]'];
						else
							$array_substitution[] = $row['[course_begin]'];
					break;
					
					default:
						$array_substitution[] = $param;
					break;
				}
			
			$put = vsprintf($this->asprintf_string, $array_substitution)."\r\n";
			
			if(@fwrite($this->filehandle, $put))
				return true;
			else
				return false;
		}
	}
	
	function get_error() { return $this->last_error; }

}


/**
 * The configurator for user report connectors
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.0
 **/
class DoceboConnectorFixedTextUI extends DoceboConnectorUI {
	var $connector = NULL;
	var $post_params = NULL;
	var $sh_next = TRUE;
	var $sh_prev = FALSE;
	var $sh_finish = FALSE;
	var $step_next = '';
	var $step_prev = '';
			
	function DoceboConnectorFixedTextUI( &$connector ) {
		$this->connector = $connector;
	}
	
	function _get_base_name() { return 'csvuiconfig'; }
		
	function get_old_name() { return $this->post_params['old_name']; }
	/** 
	 * All post fields are in array 'csvuiconfig'
	 **/	 	
	function parse_input( $get, $post ) {
		
		if( !isset($post[$this->_get_base_name()]) ) {
			// first call - first step, initialize variables
			$this->post_params = $this->connector->get_config();
			$this->post_params['step'] = '0';
			$this->post_params['field_def_type'] = '1';
			$this->post_params['old_name'] = $this->post_params['name'];
			if( $this->post_params['name'] == '' ) 
				$this->post_params['name'] = $this->lang->def('_CONN_NAME_EXAMPLE');
			if( $this->post_params['filepattern'] == '' ) 
				$this->post_params['filepattern'] = $this->lang->def('_FILEPATTERN_EXAMPLE');
		} else {
			// get previous values
			$this->post_params = unserialize(urldecode($post[$this->_get_base_name()]['memory']));
			$arr_new_params = $post[$this->_get_base_name()];
			// overwrite with the new posted values
			foreach($arr_new_params as $key => $val) {
				if( $key != 'memory' ) {
					if( $key == 'field_def' ) {
				  		$val = trim(stripslashes($val), $this->post_params['field_enclosure']);
					  		
						$this->post_params[$key] = explode( $this->post_params['field_enclosure'].$this->post_params['field_delimiter'].$this->post_params['field_enclosure'], stripslashes($val));
					} else {
						$this->post_params[$key] = stripslashes($val);
					}
				}
			}
		}
		$this->_load_step_info();
	}

	function _set_step_info( $next, $prev, $sh_next, $sh_prev, $sh_finish ) {
		$this->step_next = $next;
		$this->step_prev = $prev;
		$this->sh_next = $sh_next;
		$this->sh_prev = $sh_prev;
		$this->sh_finish = $sh_finish;
	}

	function _load_step_info() {
		switch( $this->post_params['step'] ) {
			case '0':
				$this->_set_step_info( '0', '0', FALSE, FALSE, TRUE );		
			break;
		}
	}
	
	function go_next() {
		$this->post_params['step'] = $this->step_next;
		$this->_load_step_info();
	}

	function go_prev() {
		$this->post_params['step'] = $this->step_prev;
		$this->_load_step_info();		
	}
	
	function go_finish() {
		$this->filterParams($this->post_params);
		$this->connector->set_config( $this->post_params );
	}
	
	function show_next() { return $this->sh_next; }
	function show_prev() { return $this->sh_prev; }
	function show_finish() { return $this->sh_finish; }

	function get_htmlheader() {
		return '';
	}
	
	function get_html() {
	  	$out = '';
		switch( $this->post_params['step'] ) {
			case '0':
				$out .= $this->_step0();
			break;
		}
		// save parameters
		$out .=  $this->form->getHidden($this->_get_base_name().'_memory',
										$this->_get_base_name().'[memory]',
										urlencode(serialize($this->post_params)) );
		
		return $out;
	}
	
	function _step0()
	{
	  	// ---- name -----
	  	$out = $this->form->getTextfield(	$this->lang->def('_NAME'), 
											$this->_get_base_name().'_name', 
											$this->_get_base_name().'[name]', 
											255, 
											$this->post_params['name']);
		// ---- description -----
		$out .= $this->form->getSimpleTextarea( $this->lang->def('_DESCRIPTION'), 
											$this->_get_base_name().'_description', 
											$this->_get_base_name().'[description]', 
											$this->post_params['description'] );
	  	// ---- access type read/write -----
	  	$out .= $this->form->getHidden(	$this->_get_base_name().'_readwrite', 
										$this->_get_base_name().'[readwrite]',
										'2');
		
		// ---- file pattern ----
		$out .= $this->form->getTextfield( 	$this->lang->def('_FILEPATTERN'), 
											$this->_get_base_name().'_filepattern', 
											$this->_get_base_name().'[filepattern]', 
											255, 
											$this->post_params['filepattern']);
		
		// ---- method for define fields ----
	  	$out .= $this->form->getTextfield(	$this->lang->def('_STRING_FORMAT'),
		  									$this->_get_base_name().'_asprintf_string',
		  									$this->_get_base_name().'[asprintf_string]',
		  									255,
		  									$this->post_params['asprintf_string']);
		
		$out .= $this->form->getTextfield(	$this->lang->def('_STRING_PARAM'),
		  									$this->_get_base_name().'_param_string',
		  									$this->_get_base_name().'[param_string]',
		  									255,
		  									$this->post_params['param_string']);
		
		return $out;
	}
}

function fixedtext_factory() {
	return new DoceboConnectorFixedText(array());
}

function preg_ls ($path=".", $rec=false, $pat="/.*/") {
   $pat=preg_replace ("|(/.*/[^S]*)|s", "\\1S", $pat);
   while (substr ($path,-1,1) =="/") $path=substr ($path,0,-1);
   if (!is_dir ($path) ) $path=dirname ($path);
   if ($rec!==true) $rec=false;
   $d=dir ($path);
   $ret=Array ();
   while (false!== ($e=$d->read () ) ) {
       if ( ($e==".") || ($e=="..") ) continue;
       if ($rec && is_dir ($path."/".$e) ) {
           $ret=array_merge ($ret,preg_ls($path."/".$e,$rec,$pat));
           continue;
       }
       if (!preg_match ($pat,$e) ) continue;
       if (strncmp($e,'processed',9) === 0 ) continue;
       $ret[]=$path."/".$e;
   }
   return (empty ($ret) && preg_match ($pat,basename($path))) ? Array ($path."/") : $ret;
}

?>