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
 * @subpackage	ImportExport
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorCsv extends DoceboConnector {
  	var $curr_file = '';
	var $filename = '';
	var $filehandle = NULL;
	var $first_row_header = '1';	// '1' = yes, '0' = no
	var $separator = ',';
	var $enclosure = '"';
	var $cols_descriptor = NULL;
	var $row_index = 0;
	var $readwrite = 0; // read = 1, write = 2, readwrite = 3
	var $last_error = "";
	var $name = "";
	var $description = "";
	var $subpattern = '';
		
	/**
	 * This constructor require the source file name
	 * @param array $params the array of params
	 *		- 'filename' => name of the file (required)
	 *		- 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
	 *		- 'separator' => string a char with the fields separator (Optional, default = ,)
	**/
	function DoceboConnectorCsv( $params ) {
		$this->set_config( $params );
	}
	
	function get_config() {
		return array( 	'filepattern' => $this->filename, 
						'first_row_header' => $this->first_row_header,
						'field_delimiter' => $this->separator,
						'field_enclosure' => $this->enclosure,
						'field_def' => $this->cols_descriptor,
						'readwrite' => $this->readwrite,
						'name' => $this->name,
						'description' => $this->description,
						'subpattern' => $this->subpattern );
	}
	
	function set_config( $params ) {
		if( isset($params['filepattern']) ) 		$this->filename = $params['filepattern'];
		if( isset($params['readwrite']) )			$this->readwrite = (int)$params['readwrite'];
		if( isset($params['first_row_header']) )	$this->first_row_header = (int)$params['first_row_header'];
		if( isset($params['field_delimiter']) )		$this->separator = substr($params['field_delimiter'], 0, 5);
		if( isset($params['field_enclosure']) )		$this->enclosure = substr($params['field_enclosure'], 0, 5);
		if( isset($params['field_def']) )			$this->cols_descriptor = $params['field_def'];
		if( isset($params['name']) )				$this->name = $params['name'];
		if( isset($params['description']) )			$this->description = $params['description'];
	}

	function get_configUI() {
		return new DoceboConnectorCsvUI($this);
	}
	
	/**
	 * execute the connection to source
	**/
	function connect() {
		$this->close();
		
		/* search for file with pattern */
		$pat = str_replace(array("*","?"),array(".*",".{1}"),$this->filename);
		$arr_files = preg_ls (DOCEBOIMPORT_BASEDIR.$this->subpattern, false, '/'.$pat.'/');
		if( count( $arr_files ) == 0 && !$this->is_writeonly() ) {
			//$this->last_error = 'file not found: '.DOCEBOIMPORT_BASEDIR.$this->filename;
			return  DOCEBO_IMPORT_NOTHINGTOPROCESS;
		} elseif(!$this->is_writeonly()) {
		
			$this->curr_file = $arr_files[0];
		} else {
			
			$this->curr_file = DOCEBOIMPORT_BASEDIR.$this->subpattern.$pat;
		}
		/* open file */
		if( $this->is_readonly() )
			$this->filehandle = @fopen($this->curr_file, 'r');
		elseif( $this->is_writeonly() )
			$this->filehandle = @fopen($this->curr_file, 'w');
		else
			$this->filehandle = @fopen($this->curr_file, 'rw');
		
		if( $this->filehandle === FALSE ) {
			$this->last_error = 'file not opened: '.$this->curr_file;
			return FALSE;
		}
		if($this->is_writeonly()) 
			return true;
		
		/* get header if required */
		if( $this->first_row_header == '1' ) {
			$row = fgetcsv ( $this->filehandle, 10000, $this->separator);
			if( !is_array($row) ) {
				$this->last_error = 'no rows found on '.$this->curr_file;
				return FALSE;
			}		
			if( count($row) == 0 ) {
				$this->last_error = 'no rows found on '.$this->curr_file;
				return FALSE;
			}
		}
		
		return TRUE;		
	}
	
	/**
	 * execute the close of the connection 
	**/
	function close() {
		if( $this->filehandle !== NULL ) {
			if( !@fclose( $this->filehandle ) )
				return FALSE;
			if($this->is_writeonly())
			{
				rename($this->curr_file, DOCEBOIMPORT_BASEDIR.date('Ymd').basename($this->curr_file) );
			}
			else
			{
				if (file_exists(DOCEBOIMPORT_BASEDIR.'processed'.basename($this->curr_file)))
				{
				    require_once(_base_.'/lib/lib.upload.php');
					sl_unlink(DOCEBOIMPORT_BASEDIR.'processed'.basename($this->curr_file));
					rename($this->curr_file, DOCEBOIMPORT_BASEDIR.'processed'.basename($this->curr_file) );
				}
				else
					rename($this->curr_file, DOCEBOIMPORT_BASEDIR.'processed'.basename($this->curr_file) );
			}
		}
		$this->filehandle = NULL;
		$this->row_index = 0;
		return TRUE;	
	}

	/**
	 * Return the type of the connector 
	 **/
	function get_type_name() {
		return "cvs-connector";
	}	 
	
	/**
	 * Return the description of the connector 
	 **/
	function get_type_description() {
		return "connector to cvs";
	}	 	

	/**
	 * Return the name of the connection
	 **/
	function get_name() {
		return $this->name;
	}	 	

	function get_description() {
		return $this->description;
	}	 	

	function is_readonly() { return (bool)($this->readwrite & 1); }

	function is_writeonly() { return (bool)($this->readwrite & 2); }
	
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
	
	function get_first_row() {
		if( $this->filehandle === NULL ) 
			return FALSE;
		if( !@rewind($this->filehandle) )
			return FALSE;
		$row = fgetcsv ( $this->filehandle, 1024, $this->separator, ($this->enclosure === '' ? "'".$this->enclosure."'" : $this->enclosure));
		$this->row_index++;
		if( $this->first_row_header == '1' ) {
			$row = fgetcsv ( $this->filehandle, 1024, $this->separator, ($this->enclosure === '' ? "'".$this->enclosure."'" : $this->enclosure));
			$this->row_index++;
		}
		if( !is_array($row) ) 
			return FALSE;
		if( count($row) == 0 ) 
			return FALSE;
		return $row;
	}
	
	function get_next_row() {
		if( $this->filehandle === NULL ) 
			return FALSE;
		$row = fgetcsv ( $this->filehandle, 1024, $this->separator, ($this->enclosure === '' ? "'".$this->enclosure."'" : $this->enclosure));
		if( !is_array($row) ) 
			return FALSE;
		if( count($row) == 0 ) 
			return FALSE;
		$this->row_index++;
		return $row;
	}
	
	function is_eof() {
		return feof($this->filehandle);
	}
	
	function get_row_index() {
		return $this->row_index;
	}
	
	function get_tot_mandatory_cols() { return 0; }

	function add_row( $row ) {
		
		$arr_out = array_flip($this->cols_descriptor);
		foreach( $arr_out as $colname => $val ) {
		if( isset($row[$colname]) )
				$arr_out[$colname] = $row[$colname];
			else
				$arr_out[$colname] = '';
				//unset($arr_out[$colname]);
		}
		if(!is_array($arr_out)) return true;
		if(!$arr_out) return true;
		$impl = implode('', $arr_out);
		
		if(!is_string($impl)) return true;
		
		if($impl == '') return true;
		//fputcsv($this->filehandle, $arr_out, $this->separator, $this->enclosure );
		$put = $this->enclosure.implode($this->enclosure.$this->separator.$this->enclosure, $arr_out).$this->enclosure."\r\n";
		if(@fwrite($this->filehandle, $put)) return true;
		else return false;
	}
	
	function get_error() { return $this->last_error; }

}


/**
 * The configurator for user report connectors
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.0
 **/
class DoceboConnectorCsvUI extends DoceboConnectorUI {
	var $connector = NULL;
	var $post_params = NULL;
	var $sh_next = TRUE;
	var $sh_prev = FALSE;
	var $sh_finish = FALSE;
	var $step_next = '';
	var $step_prev = '';
			
	function DoceboConnectorCsvUI( &$connector ) {
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
			if( $this->post_params['subpattern'] == '' ) 
				$this->post_params['subpattern'] = '';			
			if( $this->post_params['field_def'] === NULL )
				$this->post_params['field_def'] = array('field1','field2','field3');
		} else {
			// get previous values
			$this->post_params = Util::unserialize(urldecode($post[$this->_get_base_name()]['memory']));
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
				if( $this->post_params['readwrite'] == '1' )
					$this->_set_step_info( '1', '0', TRUE, FALSE, FALSE );
				else
					$this->_set_step_info( '1', '0', TRUE, FALSE, FALSE );		
			break;
		  	case '1':
			  	$this->_set_step_info( '1', '0', FALSE, TRUE, TRUE );
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
			case '1':
				$out .= $this->_step1();
			break;
		}
		// save parameters
		$out .=  $this->form->getHidden($this->_get_base_name().'_memory',
										$this->_get_base_name().'[memory]',
										urlencode(Util::serialize($this->post_params)) );
		
		return $out;
	}
	
	function _step0() {
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
	  	$out .= $this->form->getRadioSet( 	$this->lang->def('_ACCESSTYPE'), 
		  									$this->_get_base_name().'_readwrite', 
											$this->_get_base_name().'[readwrite]',
											array( 	$this->lang->def('_READ')  => '1', 
													$this->lang->def('_WRITE') => '2'), 
											$this->post_params['readwrite']);
		
		// ---- file pattern ----
		$out .= $this->form->getTextfield( 	$this->lang->def('_FILEPATTERN'), 
											$this->_get_base_name().'_filepattern', 
											$this->_get_base_name().'[filepattern]', 
											255, 
											$this->post_params['filepattern']);
		
		// ---- method for define fields ----
		$out .= $this->form->getRadioSet( 	$this->lang->def('_FIELD_DEFINITION_TYPE'),
											$this->_get_base_name().'_def_type',
											$this->_get_base_name().'[field_def_type]',
											array( 	$this->lang->def('_MANUAL') => '1',
													$this->lang->def('_BYEXAMPLE') => '2'),
											$this->post_params['field_def_type']
										);
	  	$out .= $this->form->getTextfield(	$this->lang->def('_FIELD_DELIMITER'),
		  									$this->_get_base_name().'_field_delimiter',
		  									$this->_get_base_name().'[field_delimiter]',
		  									1,
		  									$this->post_params['field_delimiter']);
	  	$out .= $this->form->getTextfield(	$this->lang->def('_FIELD_ENCLOSURE'),
		  									$this->_get_base_name().'_field_enclosure',
		  									$this->_get_base_name().'[field_enclosure]',
		  									1,
		  									htmlentities($this->post_params['field_enclosure']));
								
	  	$out .= $this->form->getTextfield(	$this->lang->def('_FIELD_SUBPATTERN'),
		  									$this->_get_base_name().'_subpattern',
		  									$this->_get_base_name().'[subpattern]',
		  									255,
		  									htmlentities($this->post_params['subpattern']));
		  			  									
		$out .= $this->form->getRadioSet( 	$this->lang->def('_FIRST_ROW_HEADER'),
											$this->_get_base_name().'_first_row_header',
											$this->_get_base_name().'[first_row_header]',
											array( 	$this->lang->def('_YES') => '1',
													$this->lang->def('_NO') => '0'),
											$this->post_params['first_row_header']
										);		
		return $out;
	}
	
	function _step1() {
		$enclosure = htmlentities($this->post_params['field_enclosure']);
		$out = $this->form->getLineBox( 	$this->lang->def('_FIELD_DELIMITER'),
											$this->post_params['field_delimiter'] );
	  	$out .= $this->form->getLineBox(	$this->lang->def('_FIELD_ENCLOSURE'),
		  									$enclosure );
		if( $this->post_params['field_def_type'] == '2' ) {
			$path = $GLOBALS['where_files_relative'].'/common/iofiles/'.$this->post_params['subpattern'];
			$pat = str_replace(array("*","?"),array(".*",".{1}"),$this->post_params['filepattern']);
			$arr_files = preg_ls ($path, false, '/'.$pat.'/');
			if( count( $arr_files ) == 0 ) {
				$this->post_params['field_def'] = array("File not found: ".$pat);
			} else {
				$hfile = @fopen($arr_files[0],'r');
				if( $hfile === FALSE ) {
					$this->post_params['field_def'] = array("File not open: ".$arr_files[0]);
				} else {
					$this->post_params['field_def'] = fgetcsv($hfile, 1024, $this->post_params['field_delimiter'],$this->post_params['field_enclosure'] );
				  	$out .= $this->form->getLineBox(	$this->lang->def('_FILE_ANALYZED'),
		  												basename($arr_files[0]) );
					fclose($hfile);
				}
			}
		}
		$field_def = $enclosure.implode($enclosure.$this->post_params['field_delimiter'].$enclosure,$this->post_params['field_def']).$enclosure;
		
		$out .= $this->form->getTextfield( 	$this->lang->def('_FIELD_DEF'), 
											$this->_get_base_name().'_field_def', 
											$this->_get_base_name().'[field_def]', 
											1024, 
											$field_def);
		return $out;
	}
	
}

function csv_factory() {
	return new DoceboConnectorCsv(array());
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
