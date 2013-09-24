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

define('_HTML', 'html');
define('_CSV', 'csv');
define('_XLS', 'xls');

define('_CSV_SEPARATOR', ',');
define('_CSV_ENDLINE', "\r\n");

define('_REPORT_TABLE_STYLE', 'table-view');


class ReportTablePrinter {
	
	var $type;
	var $buffer;
	var $rowCounter;
	var $overflow;
	
	function ReportTablePrinter($type=_HTML, $overflow=false) {
		$this->type = $type;

		$buffer = '';
		switch ($type) {
			case _HTML:
			case _CSV:
			case _XLS:
			default: $buffer = ''; break;
		}

		$this->buffer = $buffer;
		$this->rowCounter = 0;
		$this->overflow = $overflow;
		
		$this->addReportHeader();
	}
	
	
	function addBreak() {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '<br /><br />';
			} break;
			
			case _CSV: {
				$this->buffer .= _CSV_ENDLINE._CSV_ENDLINE;
			} break;
			
			case _XLS: {
				$this->buffer .= '<br /><br />';
			} break;

			default: { } break;
		}
	}
	
	
	function addReportHeader() {
		$lang =& DoceboLanguage::createInstance('report', 'framework');
		$date = date("d/m/Y h:i:s");
		$temp = $lang->def('_CREATION_DATE');
		$content_html = '<b>'.$temp.'</b>: '.$date;
		$content_csv  = $temp.': '.$date;
		
		switch ($this->type) {
			case _HTML :
				{
					$head = '<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>';
					$this->buffer .= $head . '<p id="report_info">' . $content_html . '</p><br />';
				}
				break;
			
			case _CSV: {
				$this->buffer .= $content_csv._CSV_ENDLINE._CSV_ENDLINE;
			} break;
			
			case _XLS: {
				$head='<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>';
				$this->buffer .= $head.'<style>'.
					'td, th { border:solid 1px black; } '.
					'</style><table><tr><td>'.$content_html.'</td></tr></table><br /><br />';
			} break;
			
			default: { } break;
		}
	}
	
	function openTable($caption='', $summary='') {
		switch ($this->type) {
			
			case _HTML: {
				if ($this->overflow) $this->buffer .= '<div style="overflow:auto; padding:1px;">';
				$this->buffer .= '<div class="yui-dt">'
					.'<table class="'._REPORT_TABLE_STYLE.'" summary="'.$summary.'">';
				if ($caption!='') $this->buffer .= '<caption>'.$caption.'</caption>';
			} break;
			
			case _CSV: {
				$this->buffer .= _CSV_ENDLINE.$caption._CSV_ENDLINE;
			} break;
			
			case _XLS: {
				//$this->buffer .= '<table>';
				$this->buffer .= '<table summary="'.$summary.'">';
				if ($caption!='') $this->buffer .= '<caption>'.$caption.'</caption>';
			} break;
			
			default: { } break;
		}
	}
	
	function closeTable() {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '</table>'
					.'</div>';
				if ($this->overflow) $this->buffer .= '</div>';
			} break;
			
			case _CSV: {
			} break;
			
			case _XLS: {
				$this->buffer .= '</table>';
			} break;
			
			default: { } break;
		}
	}
	
	
	function openHeader() {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '<thead>';
			} break;
			
			case _CSV: { 
				$this->buffer .= '';
			} break;
			
			case _XLS: {
				$this->buffer .= '<thead>';
			} break;
			
			default: { } break;
		}
	
	}
	
	
	function closeHeader() {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '</thead>';
			} break;
			
			case _CSV: {
			} break;
			
			case _XLS: {
				$this->buffer .= '</thead>';
			} break;
			
			default: { } break;
		}
	}
	
	//bufferize a table header
	function addHeader(&$head) {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '<tr>';
				foreach($head as $val) {
					if (is_array($val))
						$this->buffer .= '<th'.(isset($val['style']) ? ' class="'.$val['style'].'"' : '')
							.(isset($val['colspan']) ? ' colspan="'.$val['colspan'].'"' : '')
							//.(isset($val['rowspan']) ? ' rowspan="'.$val['rowspan'].'"' : '').
							.'>'
							.'<div class="yui-dt-liner">'
							.$val['value']
							.'</div>'
							.'</th>';
					else
						$this->buffer .= '<th>'
							.'<div class="yui-dt-liner">'
							.$val
							.'</div>'
							.'</th>';
				}
				$this->buffer .= '</tr>';
			} break;
			
			case _CSV: {
				$temp=array();
				foreach($head as $val) {
					if (is_array($val)) {
						$temp[] = $val['value'];
						if (isset($val['colspan']))
							for ($i=1; $i<$val['colspan']; $i++) $temp[]='';
					} else {
						$temp[] = $val;
					}
				}
				$this->buffer .= implode(_CSV_SEPARATOR, $temp)._CSV_ENDLINE;
			} break;
			
			case _XLS: {
				$this->buffer .= '<tr>';
				foreach($head as $val) {
					if (is_array($val))
						$this->buffer .= '<th colspan="'.$val['colspan'].'">'.$val['value'].'</th>';
					else
						$this->buffer .= '<th>'.$val.'</th>';
				}
				$this->buffer .= '</tr>';
			} break;
			
			default: { } break;
		}	
	}
	
	
	
	//table body management
	
	function openBody() {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '<tbody>';
			} break;
			
			case _CSV: {
			} break;
			
			case _XLS: {
				$this->buffer .= '<tbody>';
			} break;
			
			default: { } break;
		}
	}
	
	
	
	function closeBody() {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '</tbody>';
			} break;
			
			case _CSV: {
			} break;
			
			case _XLS: {
				$this->buffer .= '</tbody>';
			} break;
			
			default: { } break;
		}	
	}
	
	//bufferize a table row
	function addLine(&$line) {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '<tr class="yui-dt-'.(++$this->rowCounter%2?'odd':'even').'">';
				foreach($line as $val) {
					if (is_array($val)) {
						$this->buffer .= '<td'.(isset($val['style']) ? ' class="'.$val['style'].'"' : '').'><div class="yui-dt-liner">'.(isset($val['value']) ? $val['value'] : '').'</div></td>';
					} else {
						$this->buffer .= '<td><div class="yui-dt-liner">'.$val.'</div></td>';
					}
				}
				$this->buffer .= '</tr>';
			} break;
			
			case _CSV: {
				$arr = array();
				foreach ($line as $val) {
					$arr[] = (is_array($val) ? (isset($val['value']) ? $val['value'] : '') : $val);
				}
				$this->buffer .= implode(_CSV_SEPARATOR, $arr)._CSV_ENDLINE;
			} break;
			
			case _XLS: {
				$this->buffer .= '<tr class="row'.( ++$this->rowCounter % 2 ? '' : '-col' ).'">';
				foreach($line as $val) {
					if (is_array($val)) {
						$this->buffer .= '<td>'.(isset($val['value']) ? $val['value'] : '').'</td>';
					} else {
						$this->buffer .= '<td>'.$val.'</td>';
					}
				}
				$this->buffer .= '</tr>';
			} break;
			
			default: { } break;
		}
	}
	
	
	//bufferize table foot
	function setFoot(&$line) {
		switch ($this->type) {
			
			case _HTML: {
				$this->buffer .= '<tfoot><tr>';
				foreach($line as $val) {
					if (is_array($val))
						$this->buffer .= '<td colspan="'.$val['colspan'].'"><div class="yui-dt-liner">'.$val['value'].'</div></td>';
					else
						$this->buffer .= '<td><div class="yui-dt-liner">'.$val.'</div></td>';
				}
				$this->buffer .= '</tr></tfoot>';
			} break;
			
			case _CSV: {
				$temp=array();
				foreach($line as $val) {
					if (is_array($val)) {
						$temp[] = $val['value'];
						if (isset($val['colspan']))
							for ($i=1; $i<$val['colspan']; $i++) $temp[]='';
					} else {
						$temp[] = $val;
					}
				}
				$this->buffer .= implode(_CSV_SEPARATOR, $temp);
			} break;
			
			case _XLS: {
				$this->buffer .= '<tfoot><tr>';
				foreach($line as $val) {
					if (is_array($val))
						$this->buffer .= '<td colspan="'.$val['colspan'].'">'.$val['value'].'</td>';
					else
						$this->buffer .= '<td>'.$val.'</td>';
				}
				$this->buffer .= '</tr></tfoot>';
			} break;
			
			default: { } break;
		}
	}
	
	
	//return buffer content
	function get() {
		return $this->buffer;
	}
	
}

?>