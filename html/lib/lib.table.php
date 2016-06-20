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
 * @package  admin-library
 * @subpackage interaction
 * @version 	$Id: lib.table.php 838 2006-12-01 17:56:20Z fabio $
 */

require_once(_base_.'/lib/lib.navbar.php');

class TableCell {

	public $cell_type;
	public $style;
	public $abbr;
	public $label;
	public $colspan;
	public $rowspan;
	public $simple_markup = false;

	/**
	 * class constructor
	 * @param string	$label		content for this table cell
	 * @param string	$celltype	one of the two type of the cell 'header' or 'normal'
	 * @param string	$colspan	colspan for this table cell
	 * @param string	$rowspan	rowspan for this table cell
	 * @param string	$style		style class for this table cell
	 *
	 * @access public
	 */
	public function __construct($label, $cell_type = 'normal', $colspan = false, $rowspan= false, $style = false) {
		$this->label = $label;
		$this->abbr = strip_tags($label);
		$this->cell_type = $cell_type;
		if($colspan != false) 	$this->colspan = (int)$colspan;
		if($rowspan != false) 	$this->rowspan = (int)$rowspan;
		if($style != false) 	$this->style = $style;
	}

	/**
	 * @param string	$label	content for this table cell
	 *
	 * @access public
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * @param string	$celltype	one of the two type of the cell 'header' or 'normal'
	 *
	 * @access public
	 */
	public function setCellType($celltype) {
		$this->cell_type = (int)$celltype;
	}

	/**
	 * @param string	$colspan	colspan for this table cell
	 *
	 * @access public
	 */
	public function setColspan($colspan) {
		$this->colspan = (int)$colspan;
	}

	/**
	 * @param string	$rowspan	rowspan for this table cell
	 *
	 * @access public
	 */
	public function setRowspan($rowspan) {
		$this->rowspan = (int)$rowspan;
	}

	/**
	 * @param string	$style	style class for this table cell
	 *
	 * @access public
	 */
	public function setStyle($style) {
		$this->style = $style;
	}


	/**
	 * @return string	a table cell
	 *
	 * @access public
	 */
	public function getCell() {

		if($this->cell_type == 'header') {

			return '<th '
				.( $this->style != '' ? ' class="'.$this->style.'"' 	: '' )
				.( $this->colspan != '' ? ' colspan="'.$this->colspan.'"' 	: '' )
				.( $this->rowspan != '' ? ' rowspan="'.$this->rowspan.'"' : '' ).'>'
				//.( !$this->simple_markup ? '<div class="yui-dt-liner"><span class="yui-dt-label">' : '' )
				.( !$this->simple_markup ? '<div class=""><span class="">' : '' )
				.( $this->label != '' ? $this->label  : '' )
				.( !$this->simple_markup ? '</span></div>' : '' )
				.'</th>';

		} else {

			return '<td '
				.( $this->style != '' ? ' class="'.$this->style.'"' 	: '' )
				.( $this->colspan != '' ? ' colspan="'.$this->colspan.'"' 	: '' )
				.( $this->rowspan != '' ? ' rowspan="'.$this->rowspan.'"' : '' ).'>'
				// .( !$this->simple_markup ? '<div class="yui-dt-liner">' : '' )
				.( !$this->simple_markup ? '<div class="">' : '' )
				.( $this->label != '' ? $this->label  : '' )
				.( !$this->simple_markup ? '</div>' : '' )
				.'</td>';
		}
	}


		/**
	 * @return string	a table cell
	 *
	 * @access public
	 */
	public function getResponsiveCell() {

		if($this->cell_type == 'header') {

			return '<div '
				.( $this->style != '' ? ' class="table-cell '.$this->style.'"' 	: '' ).'>'
				.( !$this->simple_markup ? '<div class=""><span class="">' : '' )
				.( $this->label != '' ? $this->label  : '' )
				.( !$this->simple_markup ? '</span></div>' : '' )
				.'</div>';

		} else {

			return '<div '
				.( $this->style != '' ? ' class="table-cell '.$this->style.'"' 	: '' ).'>'
				.( !$this->simple_markup ? '<div class="">' : '' )
				.( $this->label != '' ? $this->label  : '' )
				.( !$this->simple_markup ? '</div>' : '' )
				.'</div>';
		}


		return '<div '
				.( $this->style != '' 	? ' class="table-cell '.$this->style.'"' 		: '' ).'>'
				.( $this->label != '' ? $this->label  : '&nbsp;' )
				.'</div>';
	}

}

class TableRow {

	public $id;
	public $style;
	public $cells;
	public $cols;
	public $row_type;
	public $other_code;

	public function __construct($style = false, $row_type = false, $cols = false, $row_id = false, $other_code=FALSE) {
		$this->style = $style;
		$this->row_type = $row_type;
		$this->cols = (int)$cols;
		$this->id = $row_id;
		$this->other_code = strval($other_code);
	}

/*
	public function addRow($labels, $style = false, $colspan = false, $rowspan = false) {

		switch($this->row_type) {
			case 'header' : {
				$cell_type= 'header';
			};break;
			case 'expanded' :
			default : {
				$cell_type= 'normal';
			}
		}
		$i = 0;
		$cell_number = count($labels);
		foreach($labels as $k => $label) {

			$cell_style = '';
			if($i == 0) $cell_style .= 'yui-dt-first ';
			if($i == $cell_number-1) $cell_style .= 'yui-dt-last ';
			$cell_style .= ( isset($style[$i]) ? $style[$i] : '' ) ;

			$this->cells[] = new TableCell($label, $cell_type, $colspan, $rowspan, $cell_style);
			$i++;
		}
	}
*/
	function addRow($labels, $style = false, $colspan = false, $rowspan = false) {

		switch($this->row_type) {
			case 'header' : {
				$cell_type= 'header';
			};break;
			case 'expanded' :
			default : {
				$cell_type= 'normal';
			}
		}
		$i = 0;
		foreach($labels as $k => $label) {

			//manage rowspan and colspan
			$t_style = ( isset($style[$i]) ? $style[$i] : '' );
			if (is_array($label)) {
				$_rowspan = isset($label['rowspan']) ? (int)$label['rowspan'] : $rowspan;
				$_colspan = isset($label['colspan']) ? (int)$label['colspan'] : $colspan;
				$_label = isset($label['value']) ? $label['value'] : '';
				$_style = isset($label['style']) ? $label['style'] : $t_style;
			} else {
				$_rowspan = $rowspan;
				$_colspan = $colspan;
				$_label = $label;
				$_style = $t_style;
			}

			$this->cells[] = new TableCell($_label, $cell_type, $_colspan, $_rowspan, $_style );
			$i++;
		}
	}


	/**
	 * @param string	$cols	th number of cols, is used only if the row type is expanded
	 *
	 * @access public
	 */
	public function setNumCol($cols) {
		$this->cols = (int)$cols;
	}

	/**
	 * @return string	a table row
	 *
	 * @access public
	 */
	public function getRow($i = 0) {

		if(!is_array($this->cells)) return '';

		// $row = '<tr class="yui-dt-'.($i%2?'odd':'even').' '.$this->style.'"'
		$row = '<tr class="'.($i%2?'odd':'even').' '.$this->style.'"'
			.( $this->id !== false ? ' id="'.$this->id.'"' : '' )
			.(!empty($this->other_code) ? ' '.$this->other_code : '').'>'."\n";

		if($this->row_type == 'expanded') {

			$this->cells[0]->setColspan( ( $this->cols ? $this->cols : 1 ) );
			$row .= $this->cells[0]->getCell();
		} else {
			reset($this->cells);
			while(list(, $cell) = each($this->cells)) {

				$row .= $cell->getCell();
			}
		}
		$row .= '</tr>'."\n";
		return $row;
	}

	/**
	 * @return string	a table row
	 *
	 * @access public
	 */
	public function getResponsiveRow($i = 0) {

		if(!is_array($this->cells)) return '';

		$row = '<div class="table-row '.$this->style.'"'
			.( $this->id !== false ? ' id="'.$this->id.'"' : '' )
			.(!empty($this->other_code) ? ' '.$this->other_code : '').'>';

		reset($this->cells);
		while(list(, $cell) = each($this->cells)) {

			$row .= $cell->getResponsiveCell();
		}

		$row .= '</div>'."\n";
		return $row;
	}
}

class Table {

	public $nav_bar;

	public $table_style;

	public $cols;
	public $max_rows;
	public $cols_class;

	public $table_caption;
	public $table_summary;
	public $table_id=FALSE;

	public $table_head;
	public $table_body;
	public $table_footer;
	public $add_action;

	public $row_count =0;
	public $join_next_row =FALSE;
	public $force_print = false;

	/**
	 * Construct the table drawer class
	 * @param <type> $max_rows the maximum number of rows per page
	 * @param <type> $caption the table caption
	 * @param <type> $summary the tabel summary
	 */
	public function  __construct($max_rows = 10, $caption = '', $summary = '', $class = '') {

		if(!$max_rows) $max_rows = Get::sett('visuItem');

		$this->table_style 		= 'table-view '.$class;

		$this->cols 			= 0;
		$this->max_rows 		= $max_rows;
		$this->cols_class 		= array();

		$this->table_caption 	= $caption;
		$this->table_summary 	= $summary;
                                   
		$this->table_head 		= array();
		$this->table_body 		= array();
		$this->table_foot 		= array();
		$this->add_action		= array();

		$this->hide_over		= false;

		$this->nav_bar = NULL;

		/*i need this for the transiction from old to new*/
		$this->rows = 0;
		$this->maxRowsAtTime = $max_rows;
		//Util::get_css(Get::tmpl_path('base').'yui-skin/datatable.css', true, true);
	}


	public function setTableStyle($table_style) {

		$this->table_style = $table_style;
	}

	public function setMaxRows($max_rows) {

		$this->max_rows = $max_rows;
	}

	public function setCols($cols) {

		$this->cols = $cols;
	}

	public function setColsStyle($style) {

		if(is_array($style)) $this->cols_class = $style;
	}

	public function setCaption($caption) {
		$this->table_caption = $caption;
	}

	public function setSummary($summary) {
		$this->table_summary = $summary;
	}

	public function setTableId($table_id) {
		$this->table_id=$table_id;
	}

	public function getTableId() {
		return $this->table_id;
	}

	public function getRowCount() {
		return (int)$this->row_count;
	}

	public function resetRowCount() {
		return $this->row_count=0;
	}

	public function increaseRowCount() {
		return (int)$this->row_count++;
	}

	public function getJoinNextRow() {
		$res =(bool)$this->join_next_row;
		$this->join_next_row =FALSE;
		return $res;
	}

	public function setJoinNextRow() {
		$this->join_next_row =TRUE;
	}


	public function addHead($labels, $style = false) {

		if($style !== false) $this->setColsStyle($style);
		$this->setCols(count($labels));

		$row = count($this->table_head) + 1;
		$this->table_head[$row] = new TableRow('intest', 'header', $this->cols);

		$this->table_head[$row]->addRow($labels, $this->cols_class);
	}

	public function addHeadCustom($label) {

		$row = count($this->table_head) + 1;
		$this->table_head[$row]['label'] = $label;
		$this->table_head[$row]['is_string'] = true;
	}

	public function addBody($labels, $style_row = false, $style_cell = false, $row_id = false) {

		if($style_cell !== false) $this->setColsStyle($style_cell);
		if (!$this->getJoinNextRow()) {
			$this->increaseRowCount();
		}
		$row_count = $this->getRowCount();
		$row = count($this->table_body) + 1;
		if($style_row === false) {
			if($row_count % 2) $style_row = 'odd';
			else $style_row = '';
		}

		$this->table_body[$row] = new TableRow($style_row, 'normal', $this->cols, $row_id);

		$this->table_body[$row]->addRow($labels, $this->cols_class);
	}

	public function addBodyExpanded($label, $style_row = false, $other_code=FALSE) {

		if($style_row === false) $style_row = 'full_border';

		if (!$this->getJoinNextRow()) {
			$this->increaseRowCount();
		}
		$row_count = $this->getRowCount();
		$row = count($this->table_body) + 1;

		if($row_count % 2) $style_row .= ' odd';
		else $style_row .= ' ';

		$this->table_body[$row] = new TableRow($style_row, 'expanded', $this->cols, FALSE, $other_code);

		$this->table_body[$row]->addRow(array($label), array($style_row));
	}

	public function addBodyCustom($label) {

		$row = count($this->table_body) + 1;
		$this->table_body[$row]['label'] = $label;
		$this->table_body[$row]['is_string'] = true;
	}

	public function emptyBody() {

		$this->table_body = array();
	}

	public function emptyFoot() {

		$this->table_foot = array();
	}

	public function addFoot($labels, $style = false) {

		if($style !== false) $this->setColsStyle($style);

		$row = count($this->table_foot) + 1;
		$this->table_foot[$row] = new TableRow($style, 'normal', $this->cols);

		$this->table_foot[$row]->addRow($labels, $this->cols_class);
	}

	public function addFootCustom($label) {

		$row = count($this->table_head) + 1;
		$this->table_foot[$row]['label'] = $label;
		$this->table_foot[$row]['is_string'] = true;
	}

	public function addActionAdd($label, $style = false) {

		if($style === false) $style = 'table_action_add';
//		$row = count($this->table_foot) + 1;
//		$this->table_foot[$row] = new TableRow($style, 'expanded', $this->cols);
//		$this->table_foot[$row]->addRow(array($label), array($style));
		$this->add_action[] = $label;
	}

	/**
	 * @return	string	the xhtml code for the composed table
	 */
	public function getTable() {

		$this->resetRowCount();

		if(count($this->table_head) == 0 && count($this->table_foot) == 0 && count($this->table_body) == 0 && !$this->force_print) {
			return '';
		}
		//$table = '<div class="yui-dt">';
		$table = '';

		if($this->add_action && !$this->hide_over) {
			//$table .= '<div class="table-container-over">'
			$table .= '<div class="row table-actions table-actions--above">'
				.implode("\n", $this->add_action)
				.'</div>';
		}

		$table .= '<div class="table-responsive">';

		$table .= '<div class="panel panel-default">';

		if($this->table_caption != '') {
			$table .= '<div class="panel-heading clearfix">'.$this->table_caption.'</div>';
		}

		//$table .= '<div class="panel-body"></div>';

		$table .= '<table class="table table-bordered '.$this->table_style.'" ';
		$table.= ($this->getTableId() !== FALSE ? 'id="'.$this->getTableId().'" ' : "");
		$table.= 'summary="'.$this->table_summary.'" cellspacing="0">'."\n";
		// if($this->table_caption != '') {
		// 	$table .= '<caption>'.$this->table_caption.'</caption>'."\n";
		// }
		if(count($this->table_head)) {

			reset($this->table_head);
			$table .= '<thead>'."\n";
			while(list(, $row) = each($this->table_head)) {
				if(is_array($row)) $table .= $row['label'];
				else $table .= $row->getRow();
			}
			$table .= '</thead>'."\n";
		}
		if(count($this->table_foot)) {

			reset($this->table_foot);
			$table .= '<tfoot>'."\n";
			while(list(, $row) = each($this->table_foot)) {
				if(is_array($row)) $table .= $row['label'];
				else $table .= $row->getRow();
			}
			$table .= '</tfoot>'."\n";
		}
		if(count($this->table_body)) {
			$i = 0;
			reset($this->table_body);
			$table .= '<tbody>'."\n";
			while(list($k, $row) = each($this->table_body)) {
				if(is_array($row)) $table .= $row['label'];
				else $table .= $row->getRow($i++);
			}
			$table .= '</tbody>'."\n";
		}
		$table .= '</table></div>';
		if($this->add_action) {
			// $table .= '<div class="table-container-below">'
			$table .= '<div class="row table-actions table-actions--below">'
				.implode("\n", $this->add_action)
				.'</div>';
		}
		$table .= '</div>';

		return $table;
	}



	/**
	 * @return	string	the xhtml code for the composed table
	 */
	public function getResponsiveTable() {

		$this->resetRowCount();

		if(count($this->table_head) == 0 && count($this->table_foot) == 0 && count($this->table_body) == 0 && !$this->force_print) {
			return '';
		}

		$table = '';

		if($this->add_action && !$this->hide_over) {
			$table .= '<div class="row table-actions table-actions--above">'
				.implode("\n", $this->add_action)
				.'</div>';
		}

		$table .= '<div class="table-responsive">';

		$table .= '<div class="panel panel-default">';

		if($this->table_caption != '') {
			$table .= '<div class="panel-heading clearfix">'.$this->table_caption.'</div>';
		}

		$table .= '<div class="table table-bordered '.$this->table_style.'" ';
		$table .= ($this->getTableId() !== FALSE ? 'id="'.$this->getTableId().'" ' : "");
		$table .= 'summary="'.$this->table_summary.'">';

		if(count($this->table_head)) {

			reset($this->table_head);
			$table .= '<div class="table-head">';
			while(list(, $row) = each($this->table_head)) {
				if(is_array($row)) $table .= $row['label'];
				else $table .= $row->getResponsiveRow();
			}
			$table .= '</div>';
		}
		if(count($this->table_foot)) {

			reset($this->table_foot);
			$table .= '<div class="table-foot">';
			while(list(, $row) = each($this->table_foot)) {
				if(is_array($row)) $table .= $row['label'];
				else $table .= $row->getResponsiveRow();
			}
			$table .= '</div>';
		}
		if(count($this->table_body)) {
			$i = 0;
			reset($this->table_body);
			$table .= '<div class="table-body">';
			while(list($k, $row) = each($this->table_body)) {
				if(is_array($row)) $table .= $row['label'];
				else $table .= $row->getResponsiveRow($i++);
			}
			$table .= '</div>';
		}

		$table .= '</div></div>';

		if($this->add_action) {
			$table .= '<div class="row table-actions table-actions--below">'
								.implode("\n", $this->add_action)
								.'</div>';
		}

		$table .= '</div>';

		return $table;
	}




	public function initNavBar($public_name, $kind_of = false) {

		$this->nav_bar = new NavBar($public_name, $this->max_rows, 0, $kind_of);
	}

	/**
	 * @param string	$public_name 	the publiciable name
	 */
	public function setpublicName($public_name) {

		if($this->nav_bar === NULL) return;
		$this->nav_bar->setpublicName($public_name);
	}

	/**
	 * @param string	$link 	the link used in the navbar if the kindof is link
	 */
	public function setLink($link) {

		if($this->nav_bar === NULL) return;
		$this->nav_bar->setLink($link);
	}

	/**
	 * @param array	$kind_of 	the kind of nav bar(link or button)
	 */
	public function setKindOf($kind_of) {

		if($this->nav_bar === NULL) return;
		$this->nav_bar->setKindOf($kind_of);
	}

	/**
	 * @param string	$current_element 	the current first element (from 0 to $total_element)
	 * @param string	$total_element 		the numbers of all elements
	 *
	 * @return string html code for a navigation bar
	 */
	public function getNavBar($current_element, $total_element) {

		if($this->nav_bar === NULL) return '';
		$this->nav_bar->setElementTotal($total_element);

		return $this->nav_bar->getNavBar($current_element);
	}

	public function getSelectedElement($public_name = false, $kind_of = false) {

		return $this->nav_bar->getSelectedElement($public_name, $kind_of);
	}

	public function getSelectedPage($public_name = false, $kind_of = false) {

		return $this->nav_bar->getSelectedPage($public_name, $kind_of);
	}

	public function asSelected($public_name = false, $kind_of = false) {

		return $this->nav_bar->asSelected($public_name, $kind_of);
	}












































	/******************************************************************/
	/*this is for transition before old Table to the new one*********/
	/******************************************************************/


	public function openTable( $title, $summary = '' ) {
		// EFFECTS: write title if exists and open the table
		global $module_cfg;
		return '<table class="table-view" cellspacing="0" summary="'.( $summary != '' ? $summary : 'table summary' ).'">'."\n"
				.( ($title != '') ? '<caption>'.$title.'</caption>' : '' );
	}

	public function closeTable() {
		// EFFECTS: close the table
		return '</tbody>'
			.'</table></div></div></div>'."\n\n";
	}

	public function writeHeader($colElem, $colsType) {
		// EFFECTS: write the header of the table

		$this->typeCol = $colsType;
		$code = '<thead>'."\n"
			.'<tr>'."\n";
		while(list($key, $contentCell) = each($colElem)) {
			++$this->cols;
			$code .= "\t".'<th';
			if (trim($colsType[$key]) != '') {
				switch(trim($colsType[$key])) {
					case "img" : $code .= ' class="image"';break;
					default : $code .= ' class="'.$colsType[$key].'"';
				}
			}
			$code .= '>'."\n"
				."\t\t".$contentCell."\n"
				."\t".'</th>'."\n";
		}
		return $code.'</tr>'."\n"
				.'</thead>'."\n"
				.'<tbody>'."\n";
	}

	public function setTypeCol( $colsType ) {
		//EFFECTS: assign cols type

		$this->typeCol = $colsType;
	}

	/**
 	 * public function WriteHeaderCss
	 *
	 * @param $colElem an array of column headers
	 * 	each element must contain:
	 *	['hLable'] => header Lable
	 *	['hClass'] => header Class
	 *	['toDisplay'] => toDysplay true or false
	 *	['sortable'] => sortable true or false
	 **/
	public function writeHeaderCss($colElem) {
		//EFFECTS: write the header of the table
		$code = '<thead>'."\n"
			.'<tr>'."\n";
		while(list($key, $contentCell) = each($colElem)) {
			if( $contentCell['toDisplay'] ) {
				++$this->cols;
				$code .= '<th'.($contentCell['hClass'] != '' ? ' class="'.$contentCell['hClass'].'"' : '' ).'>'
					.$contentCell['hLabel']
					.'</th>';
			}
		}
		return $code.'</tr>'."\n"
				.'</thead>'."\n"
				.'<tbody>'."\n";
	}

	public function writeRow($colsContent) {
		//EFFFECTS: write the row
		$code = '<tr class="line'.($this->rows % 2? '' : '-col' ).'">'."\n";
		while(list($key, $contentCell) = each($colsContent)) {
			$code .= "\t".'<td';
			if (trim($this->typeCol[$key]) != '') {
				switch(trim($this->typeCol[$key])) {
					case "img" : $code .= ' class="image"';break;
					default : $code .= ' class="'.$this->typeCol[$key].'"';
				}
			}
			$code .='>'."\n"
				."\t\t".(($contentCell != '') ? $contentCell : '&nbsp;')."\n"
				."\t".'</td>'."\n";
		}
		++$this->rows;
		return $code.'</tr>'."\n";
	}

	/**
 	 * public function WriteRowCss
	 *
	 * @param $colElem an array of field
	 * 	each element must contain:
	 *	['data'] => string to print out
	 *	['filedClass'] => header Class
	 *	['toDisplay'] => toDysplay true or false
	 *	['sortable'] => sortable true or false
	 **/
	public function writeRowCss($colElem) {
		//EFFFECTS: write the row
		$code = '<tr class="line'.($this->rows % 2? '' : '-col' ).'">';
		while(list($key, $contentCell) = each($colElem)) {
			if( $contentCell['toDisplay'] ) {
				$code .= '<td'.($contentCell['fieldClass'] != '' ? ' class="'.$contentCell['fieldClass'].'"' : '' ).'>'
					.$contentCell['data']
					.'</td>';
			}
		}
		$code .= '</tr>';
		++$this->rows;
		return $code;
	}

	public function writeAddRow($text) {
		//write the bar for navigate the result
		return '<tr class="table-view-add-row">'
			.'<td colspan="'.$this->cols.'">'.$text.'</td>'
			.'</tr>';
	}

	public function writeNavBar($symbol, $link, $actualRow, $totalRow, $existNext = false) {
		//	$symbols = array(
		//		'start' => '',
		//		'prev' => '',
		//		'next' => '',
		//		'end' => ''
		//	);
		//EFFECTS: write the navbar

		//math for number of page
		if($this->maxRowsAtTime == 0) return;
		if( !is_array($symbol) ) {
			$symbol = array(
				'start' => '<img src="'.getPathImage().'standard/start.gif" alt="'. Lang::t('_START').'" title="'. Lang::t('_START').'" />',
				'prev' => '<img src="'.getPathImage().'standard/prev.gif" alt="'. Lang::t('_PREV').'" title="'. Lang::t('_PREV').'" />',
				'next' => '<img src="'.getPathImage().'standard/next.gif" alt="'. Lang::t('_NEXT').'" title="'. Lang::t('_NEXT').'" />',
				'end' => '<img src="'.getPathImage().'standard/end.gif" alt="'. Lang::t('_END').'" title="'. Lang::t('_END').'" />'
			);
		}
		$nav = '';
		if($totalRow) {
			//if i have the number of the result i can write the navbar with the page number
			$numberOfPage = (int)(($totalRow / $this->maxRowsAtTime) + (($totalRow % $this->maxRowsAtTime) ? 1 : 0));
			$currentPage = (int)($actualRow / $this->maxRowsAtTime) + 1;

			if ($numberOfPage <= 7) {
				$start = 1;
				$end = $numberOfPage;
			}
			else {
				$start = (($currentPage - 3 < 1) ? 1 : $currentPage - 3);
				$end = (($currentPage + 3 > $numberOfPage) ? $numberOfPage : $currentPage + 3);
			}
			//total number of page
			$nav .= '<div class="nav-bar">';

			$nav .= '<div class="float_right">'
				. Lang::t('_RE').$totalRow.' '
				. Lang::t('_PAGES').$numberOfPage.'</div>';

			//jump to start position
			if($start != '1') $nav .= '<a href="'.$link.'0">'.$symbol['start'].'</a>&nbsp;';
			//jump one backward
			if($currentPage != '1') $nav .= '<a href="'.$link.($actualRow - $this->maxRowsAtTime).'">'.$symbol['prev'].'</a>&nbsp;';
			$nav .= '(&nbsp;';
			if($start != '1') $nav .= '...&nbsp;';

			//print pages numbers
			for($page = $start; $page <= $end; $page++) {
				if($page == $currentPage) $nav .= '<span class="current">[ '.$page.' ]</span> ';
				else {
					$nav .= '<a href="'.$link.(($page - 1) * $this->maxRowsAtTime).'">'.$page.'</a>&nbsp;';
				}

			}

			if(($page - 1) != $numberOfPage) $nav .= '...&nbsp;';
			$nav .= ')&nbsp;';
			//jump one forward
			if($currentPage != $numberOfPage) $nav .= '<a href="'.$link.($actualRow + $this->maxRowsAtTime).'">'.$symbol['next'].'</a>';
			//jump to end position
			if(($page - 1) != $numberOfPage) $nav .= '&nbsp;<a href="'.$link.(($numberOfPage - 1) * $this->maxRowsAtTime).'">'.$symbol['end'].'</a>';
			$nav .= '<div class="nofloat"></div></div>';
		}
		else {
			//if i haven't the number of result
			if(($actualRow != '0') || $existNext) $nav .= '<div class="nav-bar">';

			if($actualRow != '0') $nav .= '<a href="'.$link.($actualRow - $this->maxRowsAtTime).'">'.$symbol['prev'].'</a>&nbsp;';
			if(($actualRow != '0') && $existNext)$nav .= ' -------- ';
			//jump one forward
			if($existNext) $nav .= '<a href="'.$link.($actualRow + $this->maxRowsAtTime).'">'.$symbol['next'].'</a>';

			if(($actualRow != '0') || $existNext) $nav .= '</div>';
		}
		return $nav;
	}
}

?>