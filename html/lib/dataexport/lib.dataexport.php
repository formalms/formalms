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
 * Export datatype
 */
define('DATATYPE_HTM', "htm");
define('DATATYPE_XML', "xml");
define('DATATYPE_CSV', "csv");
define('DATATYPE_XLS', "xls");
define('DATATYPE_PDF', "pdf");

/**
 * This class serve as an abstraction for the colum information
 */
class DataColumn {

	protected $key;
	protected $label;
	protected $type;
	protected $formatter;
	protected $arguments;

	/**
	 * @param <string> $key the identifier for this colum
	 * @param <string> $label the label for the colum
	 * @param <string> $formatter the name of a function to be used in export for data formatting
	 */
	public function __construct($key, $label = false, $formatter = false) {
		$this->key = $key;
		if ($label !== false) $this->label = $label;
		else $this->label = '';
		if ($formatter != false) $this->formatter = $formatter;
		else $this->formatter = false;
		$this->arguments = null;
		//$this->setType($type);
	}

	/**
	 * Setter for the key param
	 * @return <string>
	 */
	public function setKey($key) { $this->key = (string)$key; }

	/**
	 * Getter for the key param
	 * @return <string>
	 */
	public function getKey() { return $this->key; }

	/**
	 * Setter for the label param
	 * @return <string>
	 */
	public function setLabel($label) { $this->label = (string)$label; }

	/**
	 * Getter for the label param
	 * @return <string>
	 */
	public function getLabel() { return $this->label; }

	/**
	 * Getter for the type param
	 * @return <string>
	 */
	public function getType() { return $this->type; }
	
	/**
	 * Setter for the type param
	 * @return <string>
	 */
	public function setType($type) {
		switch ($type) {
			case DOTY_FLOAT: $this->type = DOTY_FLOAT; break;
			case DOTY_INT: $this->type = DOTY_INT; break;
			case DOTY_STRING: $this->type = DOTY_STRING; break;
			default: $this->type = DOTY_MIXED;
		}
	}

	/**
	 * Setter for the formatter
	 * @return <string>
	 */
	public function setFormatter($formatter) { $this->formatter = $formatter; }

	/**
	 * Getter for the formatter
	 * @return <string>
	 */
	public function getFormatter() { return $this->formatter; }

	/**
	 * Setter for the formatter arguments
	 * @return <string>
	 */
	public function setFormatterArguments(&$args) {
		$this->arguments = &$args;
	}

	/**
	 * Return the data to be printed into the cell (formatter applied)
	 * @param <array> $record the line data
	 * @param <string> $data  the cell data
	 * @return <type>
	 */
	public function getCellData(&$record, $data) {
		if ($this->formatter != false) {
			return $this->formatter($this, $record, $data);
		} else {
			return $data;
		}
	}
	
}

/**
 * Data colum group class
 * You must use this class if you wish to setup a group of colum
 */
class DataColumnGroup {
	
	protected $key;
	protected $label;
	protected $subColumns;

	/**
	 * Constructor for the group
	 * @param <string> $key colum reference, can be blank
	 * @param <string> $label label for the colum
	 * @param <array> $subcolumns an array od DataColumn or DataColumnGroup
	 */
	public function __construct($key, $label, $subcolumns = false) {
		$this->key = $key;
		$this->label = $label;
		$this->subColumns = array();
		if (is_array($subcolumns)) {
			foreach ($subcolumns as $column) {
				$this->addColumn($column);
			}
		}
	}

	/**
	 * Getter for the key param
	 * @return <string>
	 */
	public function getKey() { return $this->key; }

	/**
	 * Setter for the label param
	 * @return <string>
	 */
	public function setLabel($label) { $this->label = (string)$label; }

	/**
	 * Getter for the label param
	 * @return <string>
	 */
	public function getLabel() { return $this->label; }

	/**
	 * Create a new sub-colum for this group
	 * @param <DataColumn> $column a DataColumn or a DataColumnGroup
	 */
	public function addColumn($column) {
		if ($column instanceof DataColumn || $column instanceof DataColumnGroup) {
			$this->subColumns[] = $column;
		}
	}

	/**
	 * Return all the subColums
	 * @return <array> an array of DataColumn or DataColumnGroup
	 */
	public function getSubColumns() { return $this->subColumns; }

	/**
	 * Return all the key for the subcolum setted
	 * @return <array> all the keys for the subcolums setted
	 */
	public function getSubColumnsKeys() {
		$keys = array();
		$i = 0;
		$count = count($this->subColumns);
		while ($i < $count) {
			if ($this->subColumns[$i] instanceof DataColumnGroup) {
				//$arr = $column->getSubColumnsKeys();
				$keys = array_merge($keys, $$this->subColumns[$i]->getSubColumnsKeys());
			} elseif ($this->subColumns[$i] instanceof DataColumn) {
				$keys[$this->subColumns[$i]->getKey()] = &$this->subColumns[$i];
			}
			$i++;
		}
		return $keys;
	}

	/**
	 * Return the depth level for the colum group
	 * @return <int>
	 */
	public function getDepth() {
		$depth = 1;
		foreach ($this->subColumns as $column) {
			if ($column instanceof DataColumnGroup) {
				$groupDepth = $column->getDepth();
				if ($groupDepth > $depth)
					$depth = $groupDepth;
			}
		}
		return $depth;
	}

	/**
	 * Return the effective colum count for this group
	 * @return <int>
	 */
	public function getColumnsCount() {
		$count = 0;
		foreach ($this->subColumns as $column) {
			if ($column instanceof DataColumnGroup)
				$count += $column->getColumnsCount();
			else
				$count++;
		}
		return $count;
	}

}

/**
 * Data source, represent a generic source of data, must be used in order to apply a dataexport
 */
class DataSource {

	protected $data;

	/**
	 * Setup the source
	 * @param <mixed> $data initialization data
	 */
	public function __construct(&$data = false) {
		$this->data = false;
	}

	/**
	 * Return the total number of rows
	 * @return <int>
	 */
	public function getTotalRows() {
		return 0;
	}

	/**
	 * Return the next set of data if exist, false otherwise
	 * @return <mixed>
	 */
	public function fetchRow() {
		return false;
	}

	/**
	 * Return the row number
	 * @return <int>
	 */
	public function getRowIndex() {
		return 0;
	}

	/**
	 * Set the row number
	 * @return <bool>
	 */
	public function setRowIndex() {
		return true;
	}

	/**
	 * Reset the data source
	 * @return <bool>
	 */
	public function reset() { return true; }
	
}

/**
 * Query extension for the data source
 */
class DataSource_Query extends DataSource {

	public function __construct(&$data) {
		parent::__construct($data);
		if (is_string($data)) {
			$this->data = sql_query($data);
		}
	}

	public function getTotalRows() {
		if (!$this->data) return 0;
		return sql_num_rows($this->data);
	}

	public function fetchRow($assoc = true) {
		if (!$this->data) return false;
		if ($assoc) {
			$output = sql_fetch_assoc($this->data);
			return $output;
		} else {
			$output = sql_fetch_row($this->data);
			return $output;
		}
	}

	public function getRowIndex() {
		if (!$this->data) return false;
		//get row_number from query result object ...
	}

	public function setRowIndex($offset) {
		if (!$this->data) return false;
		return sql_field_seek($this->data, $offset);
	}

	public function reset() {
		if (!$this->data) return false;
		return sql_field_seek($this->data, 0);
	}

}

/**
 * Array extension for the data source
 */
class DataSource_Array extends DataSource {

	public function __construct(&$data) {
		parent::__construct($data);
		if (is_array($data)) {
			$this->data = $data;
		}
	}

	public function getTotalRows() {
		if (!$this->data) return 0;
		return count($this->data);
	}

	public function fetchRow($assoc = true) {
		if (!$this->data) return false;
		$output = next($this->data);
		if ($assoc)
			return $output;
		else
			return array_values($output);
	}

	public function getRowIndex() {
		if (!$this->data) return false;
		return current($this->data);
	}

	public function setRowIndex($offset) {
		if (!$this->data) return false;
		//($this->data, $offset); //set internal point of array ...
	}

	public function reset() {
		if (!$this->data) return false;
		$output = reset($this->data);
		if ($output === false)
			return false;
		else
			return true;
	}

}

/**
 * Abstract class for data export
 */
class DataWriter {
	protected $caption;

	public function __construct($caption = '') {}

	public function getType() { return ''; }

	public function renderHead(&$columnSet) { return ''; }
	public function renderLine(&$line) { return ''; }
	public function renderFoot(&$foot) { return ''; }

	public function openTable() { return ''; }
	public function closeTable() { return ''; }
	public function openHead() { return ''; }
	public function closeHead() { return ''; }
	public function openBody() { return ''; }
	public function closeBody() { return ''; }
	public function openFoot() { return ''; }
	public function closeFoot() { return ''; }
}

/**
 * Export in htm
 */
class DataWriter_Htm extends DataWriter {

	protected $alternateLines;
	protected $lineCount;

	protected $style;

	public function __construct($caption = '', $alternate = true) {
		parent::__construct($caption);
		$this->alternateLines = ($alternate ? true : false);
		$this->lineCount = 0;

		$this->style = array(
			'table' => '',
			'thead' => '',
			'tbody' => '',
			'tfoot' => ''
		);
	}

	public function getType() { return DATATYPE_HTM; }

	private function _getTh($label, $colspan = 0, $rowspan = 0) {
		return '<th'.($colspan>1 ? ' colspan="'.$colspan.'"' : '').($rowspan>1 ? ' rowspan="'.$rowspan.'"' : '').'>'.$label.'</th>';
	}

	private function _writeHead(&$headRows, $lev, &$colGroup) {
		$maxlev = count($headRows);
		if ($lev >= $maxlev) return;
		$i = 0;
		$count = count($colGroup);
		while ($i < $count) {
			if ($colGroup[$i] instanceof DataColumnGroup) {
				$colspan = $colGroup[$i]->getColumnsCount();
				$headRows[$lev] .= $this->_getTh($colGroup[$i]->getLabel(), $colspan, 0);//'<th'.($colspan>1 ? ' colspan="'.$colspan.'"' : '').'>'.$column->getLabel().'</th>';
				$this->_writeHead($headRows, $lev+1, $colGroup[$i]->getSubColumns());
			} else {
				$diff = $maxlev - $lev;
				$headRows[$lev] .= $this->_getTh($colGroup[$i]->getLabel(), 0, $diff);//'<th'.($diff>1 ? ' rowspan="'.$diff.'"' : '').'>'.$column->getLabel().'</th>';
			}
			$i++;
		}
	}

	public function getStyle($tag) {
		if (isset($this->style[$tag]) && $this->style[$tag] != '')
			return $this->style[$tag];
		else
			return '';
	}

	public function setStyle($tag, $style) {
		if (in_array($tag, $this->style)) {
			$this->style[$tag] = (string)$style;
			return true;
		} else {
			return false;
		}
	}

	public function openTable() { return '<table'.($this->getStyle('table')!='' ? ' class="'.$this->getStyle('table').'"' : '').'>'; }
	public function closeTable() { return '</table>'; }

	public function openHead() { return '<thead'.($this->getStyle('thead')!='' ? ' class="'.$this->getStyle('thead').'"' : '').'>'; }
	public function closeHead() { return '</thead>'; }

	public function renderHead(&$columnSet) {
		$output = '';
		if (is_array($columnSet)) {

			//find max depth of grouped columns
			$depth = 1;
			foreach ($columnSet as $column) {
				if ($column instanceof DataColumnGroup) {
					$groupDepth = $column->getDepth();
					if ($groupDepth >= $depth)
						$depth += $groupDepth;
				}
			}
/*
			//find the number of single ungrouped columns
			$count = 0;
			foreach ($columnSet as $column) {
				if ($column instanceof DataColumnGroup)
					$count += $column->getColumnsCount();
				else
					$count++;
			}
*/
			//create space for rows management
			$headRows = array();
			for ($i=0; $i<$depth; $i++) { $headRows[] = '';	}

			//write the html in the headrows array
			$this->_writeHead($headRows, 0, $columnSet);

			$output .= '<tr>'.implode('</tr><tr>', $headRows).'</tr>';

		} elseif ($columnSet instanceof DataColumnGroup || $columnSet instanceof DataColumn) {
			//...
		}
		return $output;
	}


	public function openBody() { return '<tbody'.($this->getStyle('tbody')!='' ? ' class="'.$this->getStyle('tbody').'"' : '').'>'; }

	public function closeBody() { return '</tbody>'; }

	public function renderLine(&$line) {
		$output = '';
		$output .= '<tr class="row'.($this->lineCount % 2 ? '-col' : '' ).'">';

		foreach ($line as $index => $value) {
			if (is_array($value)) {}
			$output .= '<td>';
			$output .= $value;
			$output .= '</td>';
		}

		$output .= '</tr>';
		$this->lineCount++;
		return $output;
	}

	public function openFoot() { return '<tfoot>'; }

	public function closeFoot() { return '</tfoot>'; }

	public function renderFoot(&$foot) {
		return $this->getLine($foot);
	}

}

/**
 * xml data export
 */
class DataWriter_Xml extends DataWriter {

	protected $encode;
	protected $tags;

	public function __construct($caption = '') {
		parent::__construct($caption);
		$this->encode = "UTF-8";
		$this->tags = array(
			'table'       => 'exported',
			'caption'     => 'title',
			'head'        => 'header',
			'column'      => 'column',
			'columngroup' => 'columngroup',
			'label'       => 'label',
			'body'        => 'content',
			'row'         => 'row',
			'columndata'  => 'columndata',
			'foot'        => 'footer'
		);
	}

	public function getType() { return DATATYPE_XML; }

	public function openTable() {
		$output = '<?xml version="1.0" encoding="'.$this->encode.'" ?>';
		$output .= '<'.$this->tags['table'].'>';
		if ($this->caption != '') $output .= '<'.$this->tags['caption'].'>'.$this->caption.'</'.$this->tags['caption'].'>';
		return $output;
	}

	public function closeTable() { return '</'.$this->tags['table'].'>'; }

	public function openHead() { return '<'.$this->tags['head'].'>'; }

	public function closeHead() { return '</'.$this->tags['head'].'>'; }
	
	public function openBody() { return '<'.$this->tags['body'].'>'; }

	public function closeBody() { return '</'.$this->tags['body'].'>'; }
	
	public function openFoot() { return '<'.$this->tags['foot'].'>'; }

	public function closeFoot() { return '</'.$this->tags['foot'].'>'; }

	private function _renderColumns($columns) {
		if (!is_array($columns)) return '';
		$output = '';
		$i = 0;
		$count = count($columns);
		while ($i < $count) {
			if ($columns[$i] instanceof DataColumnGroup) {
				$output .= '<'.$this->tags['columngroup'].' refkey="'.$columns[$i]->getKey().'">';
				$output .= '<'.$this->tags['label'].'>'.$columns[$i]->getLabel().'</'.$this->tags['label'].'>';
				$output .= $this->_renderColumns($columns[$i]->getSubcolumns());
				$output .= '</'.$this->tags['columngroup'].'>';
			} elseif ($columns[$i] instanceof DataColumn) {
				$output .= '<'.$this->tags['column'].' refkey="'.$columns[$i]->getKey().'">';
				$output .= '<'.$this->tags['label'].'>'.$columns[$i]->getLabel().'</'.$this->tags['label'].'>';
				$output .= '</'.$this->tags['column'].'>';
			}
			$i++;
		}
		return $output;
	}

	public function renderHead(&$columnSet) {
		if (!is_array($columnSet)) return '';
		$output = $this->_renderColumns($columnSet);
		return $output;
	}

	public function renderLine(&$line) {
		$output = '<'.$this->tags['row'].'>';
		foreach ($line as $key=>$value) {
			$output .= '<'.$this->tags['columndata'].' key="'.$key.'">';
			$output .= $value;
			$output .= '</'.$this->tags['columndata'].'>';
		}
		$output .= '</'.$this->tags['row'].'>';
		return $output;
	}

	public function renderFoot(&$foot) { return ''; }
	
}

/**
 * Csv data export
 */
class DataWriter_Csv extends DataWriter {

protected $delimiter;
	protected $rowDelimiter;
	protected $stringDelimiter;

	public function __construct($caption = '', $delimiter = false) {
		parent::__construct($caption);
		if ($delimiter) {
			$this->setDelimiter($delimiter);
		} else $this->setDelimiter(',');
		$this->setRowDelimiter("\r\n");
		$this->stringDelimiter = '"';
	}

	public function setDelimiter($delimiter) {
		$this->delimiter = $delimiter;
	}

	public function setRowDelimiter($delimiter) {
		$this->rowDelimiter = $delimiter;
	}

	public function getType() { return DATATYPE_CSV; }

	private function _formatCsv($value) {
		$formatted = str_replace($this->stringDelimiter, "\\".$this->stringDelimiter, htmlspecialchars_decode($value, ENT_QUOTES));
		return $this->stringDelimiter.str_replace($this->delimiter, "\\".$this->delimiter, $formatted).$this->stringDelimiter;
	}

	private function _renderColumns(&$headRows, $lev, &$columns) {
		$max = count($headRows);
		if ($lev >= $max) return;
		$i = 0;
		$count = count($columns);
		while ($i < $count) {
			if ($columns[$i] instanceof DataColumnGroup) {
				$num = $columns[$i]->getColumnsCount();
				for ($j=0; $j<$num; $j++) $headRows[$lev][] = $this->_formatCsv($columns[$i]->getLabel());
				$this->_renderColumns($headRows, $lev+1, $columns[$i]->getSubColumns());
			} elseif ($columns[$i] instanceof DataColumn) {
				//$headRows[$lev][] = $this->_formatCsv($columns[$i]->getLabel());
				//for ($j=$lev+1; $j<$max; $j++) { $headRows[$j][] = $this->_formatCsv(''); }
				for ($j=$lev; $j<$max-1; $j++) { $headRows[$j][] = $this->_formatCsv(''); }
				$headRows[$max-1][] = $this->_formatCsv($columns[$i]->getLabel());
			}
			$i++;
		}
	}

	public function renderHead(&$columnSet) {
		$output = '';

		if (is_array($columnSet)) {
			$i = 0;
			$count = count($columnSet);

			$depth = 1;
			while ($i < $count) {
				if ($columnSet[$i] instanceof DataColumnGroup) {
					$groupDepth = $columnSet[$i]->getDepth();
					if ($groupDepth >= $depth)
						$depth += $groupDepth;
				}
				$i++;
			}

			$headRows = array();
			for ($i=0; $i<$depth; $i++) { $headRows[] = array();	}

			$this->_renderColumns($headRows, 0, $columnSet);

			$rowsOutput = array();
			for ($i=0; $i<count($headRows); $i++) {
				$rowsOutput[] = implode($this->delimiter, $headRows[$i]).$this->rowDelimiter;
			}
			$output .= implode('', $rowsOutput);
		}

		return $output;
	}

	public function renderLine(&$line) {
		$output = '';

		$values = array();
		foreach ($line as $index => $value) {
			//$formatted = str_replace($this->stringDelimiter, "\\".$this->stringDelimiter, htmlspecialchars_decode($value, ENT_QUOTES));
			//$values[] = $this->stringDelimiter.str_replace($delimiter, "\\".$delimiter, $formatted).$this->stringDelimiter;
			$values[] = $this->_formatCsv($value);
		}
		$output .= implode($this->delimiter, $values).$this->rowDelimiter;

		return $output;
	}

	public function renderFoot(&$foot) { return ''; }

}

/**
 * Xls data export
 */
class DataWriter_Xls extends DataWriter_Htm {
	public function getType() { return DATATYPE_XLS; }
}
/*
class DataWriter_Xls extends DataWriter {
	protected $lineIndex;
	protected $workBook;
	protected $workSheet;

	public function __construct($caption = '') {
		parent::__construct($caption);
		$this->lineIndex = 0;
		$this->workBook = new WorkBook("_");
		$this->workSheet =& $this->workBook->add_worksheet(($caption!='' ? $caption : 'WorkSheet'));
	}

	public function getType() { return DATATYPE_XLS; }


	private function _formatCellContent($data, $type = false) {
		return $data;
	}
	
	public function openTable() { return ''; }
	public function closeTable() { return ''; }
	public function openHead() { return ''; }
	public function closeHead() { return ''; }
	public function openBody() { return ''; }
	public function closeBody() { return ''; }
	public function openFoot() { return ''; }
	public function closeFoot() { return ''; }


	public function renderHead(&$columnSet) {
		if (!$this->workSheet) return '';

		return '';
	}

	public function renderLine(&$line) {
		if (!$this->workSheet) return '';
		$row = $this->lineIndex;
		$col = 0;
		foreach ($line as $key=>$value) {
			$this->workSheet->write($row, $col, $this->_formatCellContent($value));
			$col++;
		}
		$this->lineIndex++;
		return '';
	}

	public function renderFoot(&$foot) { return ''; }

}*/

/**
 * Exporter
 */
class DataExport {

	protected $id;
	protected $columns;
	protected $dataSource;
	protected $writer;

	protected $fieldsList;
	protected $buffer;

	public function __construct($type, $id, &$columns, &$dataSource) {
		$this->id = (string)$id;
		
		if ($this->_validateColumns($columns)) $this->columns = $columns; else $this->columns = array();
		$fields = array();
		$i = 0;
		$count = count($this->columns);
		while ($i < $count) {
			if ($this->columns[$i] instanceof DataColumnGroup) {
				$fields = array_merge($fields, $this->columns[$i]->getSubColumnsKeys());
			} elseif ($this->columns[$i] instanceof DataColumn) {
				$fields[$this->columns[$i]->getKey()] = &$this->columns[$i];
			}
			$i++;
		}
		$this->fieldsList = $fields;

		if (is_subclass_of($dataSource, 'DataSource'))	$this->dataSource = $dataSource; else $this->dataSource = new DataSource();

		$this->buffer = '';

		switch ($type) {
			case DATATYPE_HTM : $this->writer = new DataWriter_Htm(); break;
			case DATATYPE_XML : $this->writer = new DataWriter_Xml(); break;
			case DATATYPE_CSV : $this->writer = new DataWriter_Csv(); break;
			case DATATYPE_XLS : $this->writer = new DataWriter_Xls(); break;
			//case DATATYPE_PDF : $this->writer = new DataWriter_Pdf(); break;
			default: $this->writer = new DataWriter(); break;
		}
	}

	private function _validateColumns($columns) {
		$result = true;
		//...
		return $result;
	}

	private function _write($data, $print) {
		//echo htmlspecialchars($data).'<br />'; //DEBUG...
		if ($print)
			cout($data);
		else
			$this->buffer .= $data;
	}

	public function setWriterOption($name, $value) {
		if (is_string($name))
			$this->writer->$name = $value;
	}

	public function setWriterOptions($options) {
		if (is_array($options))
			foreach ($options as $name=>$value)
				$this->setWriterOption($name, $value);
	}

	public function render($print = false) {

		$this->_write($this->writer->openTable(), $print);

		$this->_write($this->writer->openHead(), $print);
		$head = $this->writer->renderHead($this->columns);
		$this->_write($head, $print);
		$this->_write($this->writer->closeHead(), $print);

		$this->_write($this->writer->openBody(), $print);
		while ($row = $this->dataSource->fetchRow()) {
			$line = array();
			//check columns keys with row keys and filter the data
			foreach ($this->fieldsList as $field=>$column) {
				$value = '';
				if (array_key_exists($field, $row)) {
					$value = $column->getCellData($row, $row[$field]);
				}
				$line[$field] = $value;
			}
			$this->_write($this->writer->renderLine($line), $print);
		}
		$this->_write($this->writer->closeBody(), $print);

		$this->_write($this->writer->closeTable(), $print);

		if (!$print) return $this->buffer;
	}
	
}

?>