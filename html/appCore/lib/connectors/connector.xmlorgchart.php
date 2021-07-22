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

require_once( dirname(__FILE__) . '/lib.connector.php' );

/** 
 * class for define csv connection to data source.
 * @package admin-core
 * @subpackage io-operation
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorXmlOrgChart extends DoceboConnector {
  	var $curr_file = '';
	var $filename = '';
	var $dom_doc = NULL;
	var $folder_nodes = NULL;
	
	var $cols_descriptor = NULL;
	var $row_index = 0;
	var $readwrite = 0; // read = 1, write = 2, readwrite = 3
	var $last_error = "";
	var $name = "";
	var $description = "";
		
	function DoceboConnectorXmlOrgChart( $params ) {
		$this->set_config( $params );
	}
	
	function get_config() {
		return array( 	'filepattern' => $this->filename, 
						'readwrite' => $this->readwrite,
						'name' => $this->name,
						'description' => $this->description );
	}
	
	function set_config( $params ) {
		if( isset($params['filepattern']) ) 		$this->filename = $params['filepattern'];
		if( isset($params['readwrite']) )			$this->readwrite = $params['readwrite'];
		if( isset($params['name']) )				$this->name = $params['name'];
		if( isset($params['description']) )			$this->description = $params['description'];
	}

	function get_configUI() {
		return new DoceboConnectorXmlOrgChartUI($this);
	}
	
	/**
	 * execute the connection to source
	**/
	function connect() {
		$this->close();
		/* search for file with pattern */
		$pat = str_replace(array("*","?"),array(".*",".{1}"),$this->filename);
		$arr_files = $this->_preg_ls (DOCEBOIMPORT_BASEDIR, false, '/^'.$pat.'/');
		if( count( $arr_files ) == 0 ) {
			//$this->last_error = 'file not found: '.DOCEBOIMPORT_BASEDIR.$this->filename;
			return DOCEBO_IMPORT_NOTHINGTOPROCESS;
		}
		$this->curr_file = $arr_files[0];
		require_once(_base_.'/lib/lib.domxml.php');
		$this->dom_doc = new DoceboDOMDocument();
		$this->dom_doc->loadXML(file_get_contents($this->curr_file));
		//$this->dom_doc = DoceboDOMDocument::loadXML($this->curr_file);
		//$error = '';
		//$this->dom_doc = domxml_open_file($this->curr_file,DOMXML_LOAD_VALIDATING ,$error);

		if( $this->dom_doc === NULL ) {
			$this->last_error = 'Error parsing xml org chart file: '.DOCEBOIMPORT_BASEDIR.$this->curr_file;
			return FALSE;
		}
		
		$this->folder_nodes = $this->dom_doc->getElementsByTagName('folder');
		
		return TRUE;		
	}
	
	/**
	 * execute the close of the connection 
	**/
	function close() {
		if( $this->dom_doc !== NULL ) {
			$this->dom_doc = NULL;
			rename($this->curr_file, DOCEBOIMPORT_BASEDIR.'processed'.basename($this->curr_file) );
		}
		$this->row_index = 0;
		return TRUE;	
	}

	/**
	 * Return the type of the connector 
	 **/
	function get_type_name() {
		return "xmlorgchart-connector";
	}	 
	
	/**
	 * Return the description of the connector 
	 **/
	function get_type_description() {
		return "connector to xml organization chart descriptor";
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
	
	function is_raw_producer() { return TRUE; }
	
	function _get_folder_data() {
		$arr_data = array();
		$elem_folder = $this->folder_nodes->item($this->row_index);
		if( $elem_folder === NULL )
			return FALSE;
		// extract folder title in other languages
		$title_nodes = $elem_folder->getElementsByTagName('title');
		$arr_data['lang_titles'] = array();
		for( $indexLang = 0; $indexLang < $title_nodes->length; $indexLang++ ) {
			$elem_title = $title_nodes->item($indexLang);
			$arr_data['lang_titles'][$elem_title->getAttribute('lang')] = $elem_title->firstChild->nodeValue;
		}
		// extract folder fields
		$child_list = $elem_folder->childNodes;
		$elem_fields = NULL;
		for( $indexChild = 0; $indexChild < $child_list->length; $indexChild++ ) {
			$curr_child = $child_list->item($indexChild);
			$nodeType = $curr_child->nodeType;
			if( $nodeType == XML_ELEMENT_NODE ) {
				$tagName = $curr_child->tagName;
				if( $tagName == 'fields' ) {
					$elem_fields = $curr_child;
					break;
				}
			}
		}
		if( $elem_fields !== NULL ) {
			$field_nodes = $elem_fields->getElementsByTagName('field');
			$arr_data['custom_fields'] = array();
			for( $indexField = 0; $indexField < $field_nodes->length; $indexField++ ) {
				$elem_field = $field_nodes->item($indexField);
				$field_name = FALSE;
				$field_value = FALSE;
				$field_mandatory = ($elem_field->getAttribute('mandatory')=='yes');
				$child_field = $elem_field->firstChild;
				$nodeType = $child_field->nodeType;
				
				if( $nodeType == XML_ELEMENT_NODE )
					$tagName = $child_field->tagName;
				else
					$tagName = '';
	
				while($child_field !== NULL && $child_field !== FALSE ) {
					if( $child_field->nodeType == XML_ELEMENT_NODE ) {
						switch( $tagName ) {
							case 'name':
								$field_name = $child_field->textContent;
							break;
							case 'value':
								$field_value = $child_field->textContent;
							break;
						}
					}
					$child_field = $child_field->nextSibling;
					$nodeType = $child_field->nodeType;
					if( $nodeType == XML_ELEMENT_NODE )
						$tagName = $child_field->tagName;
					else
						$tagName = '';
				}
				if($field_name !== FALSE && $field_value !== FALSE )
					$arr_data['custom_fields'][$field_name] = array('fvalue'=>$field_value, 'mandatory' => $field_mandatory );
			}
		}
		$path_elem = array();
		$path_elem[] = $elem_folder->getAttribute('name');
        $arr_data['code']= $elem_folder->getAttribute('code');        
		$parent = $elem_folder->parentNode;
		while( $parent->tagName == 'folder' ) {
			$path_elem[] = $parent->getAttribute('name');
			$parent = $parent->parentNode;
		}
		$path_elem = array_reverse($path_elem);
		$path = implode('/',$path_elem);
		$arr_data['path'] = $path;
		
		return $arr_data;
	}
	
	function get_first_row() {
		if( $this->dom_doc === NULL ) 
			return FALSE;
		$this->row_index = 0;
		return $this->_get_folder_data();
	}
	
	function get_next_row() {
		if( $this->dom_doc === NULL ) 
			return FALSE;
		$this->row_index++;
		return $this->_get_folder_data();
	}
	
	function is_eof() {
		return ($this->row_index >= $this->folder_nodes->length);
	}
	
	function get_row_index() {
		return $this->row_index;
	}
	
	function get_tot_mandatory_cols() { return 0; }

	function add_row( $row ) {
		return false;
	}
	
	function get_error() { return $this->last_error; }

	function _preg_ls ($path=".", $rec=false, $pat="/.*/") {
	   $pat=preg_replace ("|(/.*/[^S]*)|s", "\\1S", $pat);
	   while (substr ($path,-1,1) =="/") $path=substr ($path,0,-1);
	   if (!is_dir ($path) ) $path=dirname ($path);
	   if ($rec!==true) $rec=false;
	   $d=dir ($path);
	   $ret=array();
	   while (false!== ($e=$d->read () ) ) {
	       if ( ($e==".") || ($e=="..") ) continue;
	       if ($rec && is_dir ($path."/".$e) ) {
	           $ret=array_merge ($ret,$this->_preg_ls($path."/".$e,$rec,$pat));
	           continue;
	       }
	       if (!preg_match ($pat,$e) ) continue;
	       if (strncmp($e,'processed',9) === 0 ) continue;
	       $ret[]=$path."/".$e;
	   }
	   return (empty ($ret) && preg_match ($pat,basename($path))) ? Array ($path."/") : $ret;
	}

}


/**
 * The configurator for csv connectors
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorXmlOrgChartUI extends DoceboConnectorUI {
	var $connector = NULL;
	var $post_params = NULL;
	var $sh_next = TRUE;
	var $sh_prev = FALSE;
	var $sh_finish = FALSE;
	var $step_next = '';
	var $step_prev = '';
			
	function DoceboConnectorXmlOrgChartUI( &$connector ) {
		$this->connector = $connector;
	}
	
	function _get_base_name() { return 'xmlocuiconfig'; }
		
	function get_old_name() { return $this->post_params['old_name']; }
	/** 
	 * All post fields are in array 'csvuiconfig'
	 **/	 	
	function parse_input( $get, $post ) {
		
		if( !isset($post[$this->_get_base_name()]) ) {
			// first call - first step, initialize variables
			$this->post_params = $this->connector->get_config();
			$this->post_params['step'] = '0';
			$this->post_params['old_name'] = $this->post_params['name'];
			if( $this->post_params['name'] == '' ) 
				$this->post_params['name'] = $this->lang->def('_CONN_NAME_EXAMPLE');
			if( $this->post_params['filepattern'] == '' ) 
				$this->post_params['filepattern'] = $this->lang->def('_FILEPATTERN_EXAMPLE');
		} else {
			// get previous values
			$this->post_params = Util::unserialize(urldecode($post[$this->_get_base_name()]['memory']));
			$arr_new_params = $post[$this->_get_base_name()];
			// overwrite with the new posted values
			foreach($arr_new_params as $key => $val) {
				if( $key != 'memory' ) {
					$this->post_params[$key] = stripslashes($val);
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
				$this->_set_step_info( '1', '0', FALSE, FALSE, TRUE );
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
		
		return $out;
	}	
}

function xmlorgchart_factory() {
	return new DoceboConnectorXmlOrgChart(array());
}

?>
