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
 * @version 	$Id: lib.navbar.php 907 2007-01-13 11:29:17Z fabio $
 */

class NavBar {

	var $symbol;
	var $var_name;

	var $element_total;
	var $element_per_page;

	var $kind_of;
	var $max_page_at_time = 9;

	var $more_page = '...';
	var $current_page = ' [current] ';

	/**
	 * class constructor
	 * @param string 	$var_name 			the variable name used for page
	 * @param int 		$element_per_page 	the number of the element displayed in a single page
	 * @param int 		$element_total 		the total number of element
	 * @param enum 		$kind_of 			the type of navbar (link or button)
	 */
	function NavBar($var_name, $element_per_page, $element_total, $kind_of = false, $modname = false, $platform = false) {

		$this->var_name			= $var_name;
		$this->element_per_page = $element_per_page;
		$this->element_total 	= $element_total;
		$this->link 			= '';

		if($kind_of == 'button') $this->kind_of = 'button';
		else $this->kind_of = 'link';

		$this->symbol = array(
			'start' => array(
				'img' => '<img src="'.getPathImage('fw').'standard/start.gif" alt="'. Lang::t('_START').'" title="'. Lang::t('_START').'" />',
				'src' => getPathImage('fw').'standard/start.gif',
				'alt' => Lang::t('_START', $modname)
			),
			'prev' => array(
				'img' => '<img src="'.getPathImage('fw').'standard/prev.gif" alt="'. Lang::t('_PREV').'" title="'. Lang::t('_PREV').'" />',
				'src' => getPathImage('fw').'standard/prev.gif',
				'alt' => Lang::t('_PREV', $modname)
			),
			'next' => array(
				'img' => '<img src="'.getPathImage('fw').'standard/next.gif" alt="'. Lang::t('_NEXT').'" title="'. Lang::t('_NEXT').'" />',
				'src' => getPathImage('fw').'standard/next.gif',
				'alt' => Lang::t('_NEXT', $modname)
			),
			'end' => array(
				'img' => '<img src="'.getPathImage('fw').'standard/end.gif" alt="'. Lang::t('_END').'" title="'. Lang::t('_END').'" />',
				'src' => getPathImage('fw').'standard/end.gif',
				'alt' => Lang::t('_END', $modname)
			)
		);
	}

	/**
	 * @param string 	$kind_of 	the type of the navbar
	 */
	function setKindOf($kind_of) {

		$this->kind_of = $kind_of;
	}

	/**
	 * @param string 	$var_name 	the variable name used for page
	 */
	function setVarName($var_name) {

		$this->var_name = $var_name;
	}

	/**
	 * @param string	$link 	the link used in the navbar if the kindof is link
	 */
	function setLink($link) {

		$this->link = $link;
	}

	/**
	 * @param string 	$total 	the elements number
	 */
	function setElementTotal($element_total) {

		$this->element_total = $element_total;
	}

	/**
	 * @param array	$symbol 	the symbol for start prev e next used in the navbar
	 */
	function setSymbol($symbol) {

		$this->symbol = $symbol;
	}

	/**
	 * @return int	return the total number of pages
	 */
	function _getNumbersOfPage() {

		$number_of_page = (int)($this->element_total / $this->element_per_page);
		$number_of_page += ( ($this->element_total % $this->element_per_page) ? 1 : 0);
		return $number_of_page;
	}

	/**
	 * @param int	$element_selected 	the number of the element actually selected
	 *
	 * @return int	return the number of the current page
	 */
	function _getCurrentPage($element_selected) {

		$current_page = (int)($element_selected / $this->element_per_page) + 1;
		return $current_page;
	}

	/**
	 * @return string	return the number of element and page
	 */
	function getInfo() {

		$pages = $this->_getNumbersOfPage();
		return '<span class="number-of-result">'. Lang::t('_RE').' : </span>'.$this->element_total.'&nbsp;'
			.'<span class="total-page">'. Lang::t('_PAGES').' : </span>'.$pages;
	}

	/**
	 * @param int	$element_selected 	the number of the element actually selected
	 *
	 * @return	string the html code for the requested navbar
	 */
	function getNavBar($element_selected = false) {

		$html = '';
		if($element_selected === false) $element_selected = $this->getSelectedElement();
		if($this->kind_of == 'link') {

			$html = $this->getNavBarLink($element_selected);
		} else {

			$html = $this->getNavBarButton($element_selected);
		}
		return $html;
	}

	function getNavBarLink($element_selected) {

		$pages 			= $this->_getNumbersOfPage();
		$now_on_page 	= $this->_getCurrentPage($element_selected);
		$gap			= (int)($this->max_page_at_time / 2);

		if($pages <= 1) return '';

		if($this->link == '') {
			$this->link = 'index.php?modname='.$GLOBALS['modname'].'&amp;op='.$GLOBALS['op'];
		}
		$effective_link = $this->link.( strstr($this->link, '?') !== false ? '&amp;' : '?' ).$this->var_name.'=';

		// Limitation of number of page displayed at time

		if($pages <= $this->max_page_at_time) {

			$start = 1;
			$end = $pages;
		} else {

			$start = ( ($now_on_page - $gap < 1) ? 1 : $now_on_page - $gap );
			$end = ( ($now_on_page + $gap > $pages) ? $pages : $now_on_page + $gap );
		}
		$html = '<div class="nav-bar">';

		// Information about the result
		$html .= '<div class="nav-info">'
			.$this->getInfo()
			.'</div>';

		$html .= '<ul class="nav-pages">';

		if($now_on_page != '1') {

			$html .= '<li><a class="nav-bar-prev" href="'.$effective_link.($now_on_page - 1).'">'
					.$this->symbol['prev']['alt']
					.'</a></li>';
		} else {

			$html .= '<li><span class="nav-bar-prev_disabled">'.$this->symbol['prev']['alt'].'</span></li>';
		}
		if($start != 1)$html .= '<li><a href="'.$effective_link.'1">1</a></li>';
		if($start > 2) $html .= $this->more_page.'';

		// Print pages numbers
		for($cursor = $start; $cursor <= $end; $cursor++) {

			if($cursor == $now_on_page) {

				$html .= '<li><span class="nav-current">'.trim(str_replace('[current]', $cursor, $this->current_page)).'</span></li>';
			} else {

				$html .= '<li><a href="'.$effective_link.$cursor.'">'.$cursor.'</a></li>';
			}
		}
		$cursor;
		if($end < $pages-1) {

			// If the last page printed is not the last page
			$html .= $this->more_page.'';
		}
		if($end != $pages) {
			$html .= '<li><a href="'.$effective_link.$pages.'">'.$pages.'</a></li>';
		}
		if($now_on_page != $pages && $pages != 0) {
			$html .= '<li><a class="nav-bar-next" href="'.$effective_link.($now_on_page + 1).'">'.$this->symbol['next']['alt'].'</a></li>';
		} else {
			$html .= '<li><span class="nav-bar-next_disabled">'.$this->symbol['next']['alt'].'</span></li>';
		}

		$html .= '</ul>';
		$html .= '<div class="nofloat"></div>'.'</div>';
		return $html;
	}


	function getNavBarButton($element_selected) {

		$pages 			= $this->_getNumbersOfPage();
		$now_on_page 	= $this->_getCurrentPage($element_selected);
		$gap			= (int)($this->max_page_at_time / 2);

		if($pages <= 1) return '';

		if($this->link == '') {
			$this->link = 'index.php?modname='.$GLOBALS['modname'].'&amp;op='.$GLOBALS['op'];
		}
		// Limitation of number of page displayed at time

		if($pages <= $this->max_page_at_time) {

			$start = 1;
			$end = $pages;
		} else {

			$start = ( ($now_on_page - $gap < 1) ? 1 : $now_on_page - $gap );
			$end = ( ($now_on_page + $gap > $pages) ? $pages : $now_on_page + $gap );
		}
		$html = '<div class="nav-bar">';

		// Information about the result
		$html .= '<div class="nav-info">'
			.$this->getInfo()
			.'</div>';

		$html .= '<ul class="nav-pages">';

		if($now_on_page != '1') {

			$html .= '<li><input class="nav-bar-prev" type="submit" '
						.'id="'.$this->var_name.'_'.($now_on_page - 1).'" '
						.'name="'.$this->var_name.'['.($now_on_page - 1).']" '
						.'value="'.$this->symbol['prev']['alt'].'" '
						.'alt="'.$this->symbol['prev']['alt'].'" />'
					.'</li>';
		} else {

			$html .= '<li><span class="nav-bar-prev_disabled">'.$this->symbol['prev']['alt'].'</span></li>';
		}
		if($start != 1)$html .= '<li><input class="nav-bar" type="submit" '
							.'id="'.$this->var_name.'_1" '
							.'name="'.$this->var_name.'[1]" '
							.'value="1" '
							.'alt="1" />'
						.'</li>';
		if($start > 2) $html .= $this->more_page.'';

		// Print pages numbers
		for($cursor = $start; $cursor <= $end; $cursor++) {

			if($cursor == $now_on_page) {

				$html .= '<li><span class="nav-current">'.trim(str_replace('[current]', $cursor, $this->current_page)).'</span></li>';
			} else {

				$html .= '<li><input type="submit" '
							.'id="'.$this->var_name.'_'.$cursor.'" '
							.'name="'.$this->var_name.'['.$cursor.']" '
							.'value="'.$cursor.'" '
							.'alt="'.$cursor.'" />'
						.'</li>';
			}
		}
		$cursor;
		if($end < $pages-1) {

			// If the last page printed is not the last page
			$html .= $this->more_page.'';
		}
		if($end != $pages) {
			$html .= '<li><input type="submit" '
						.'id="'.$this->var_name.'_'.$pages.'" '
						.'name="'.$this->var_name.'['.$pages.']" '
						.'value="'.$pages.'" '
						.'alt="'.$pages.'" />'
					.'</li>';
		}
		if($now_on_page != $pages && $pages != 0) {
			$html .= '<li><input class="nav-bar-next" type="submit" '
						.'id="'.$this->var_name.'_'.($now_on_page + 1).'" '
						.'name="'.$this->var_name.'['.($now_on_page + 1).']" '
						.'value="'.$this->symbol['next']['alt'].'" '
						.'alt="'.$this->symbol['next']['alt'].'" /></li>';
		} else {
			$html .= '<li><span class="nav-bar-next_disabled">'.$this->symbol['next']['alt'].'</span></li>';
		}

		$html .= '</ul>';
		$html .= '<div class="nofloat"></div>'.'</div>';
		return $html;

		/*
		$pages 			= $this->_getNumbersOfPage();
		$now_on_page 	= $this->_getCurrentPage($element_selected);
		$gap			= (int)($this->max_page_at_time / 2);

		// Limitation of number of page displayed at time

		if($pages <= $this->max_page_at_time) {

			$start = 1;
			$end = $pages;
		} else {

			$start = ( ($now_on_page - $gap < 1) ? 1 : $now_on_page - $gap );
			$end = ( ($now_on_page + $gap > $pages) ? $pages : $now_on_page + $gap );
		}
		$html = '<div class="nav-bar">';

		// Information about the result
		$html .= '<div class="nav-info">'
			.$this->getInfo()
			.'</div>';

		$html .= '<ul class="nav-pages">';
		if($start != '1') {

			// Link to page 1 (start) if the current page is not the first
			$html .= '<li><input class="nav-pages-bimage" type="image" src="'.$this->symbol['start']['src'].'" '
						.'id="'.$this->var_name.'_1" '
						.'name="'.$this->var_name.'[1]" '
						.'alt="'.$this->symbol['start']['alt'].'"/>'
					.'</li>';

		}
		if($now_on_page != '1') {

			// If this is not the first page print the back link
			$html .= '<li><input class="nav-pages-bimage" type="image" src="'.$this->symbol['prev']['src'].'" '
						.'id="'.$this->var_name.'_'.($now_on_page - 1).'" '
						.'name="'.$this->var_name.'['.($now_on_page - 1).']" '
						.'alt="'.$this->symbol['prev']['alt'].'" />'
					.'</li>';
		}
		if($start != '1') {

			// If the first page that is not the number 1
			$html .= $this->more_page.'&nbsp;';
		}

		// Print pages numbers

		for($cursor = $start; $cursor <= $end; $cursor++) {

			if($cursor == $now_on_page) {

				$html .= '<li><span class="nav-current">'
					.str_replace('[current]', $cursor, $this->current_page)
					.'</span></li>';
			} else {

				$html .= '<li><input class="nav-pages-button" type="submit" '
						.'id="'.$this->var_name.'_'.$cursor.'" '
						.'name="'.$this->var_name.'['.$cursor.']" '
						.'value="'.$cursor.'" /></li>';
			}

		}
		--$cursor;
		if($cursor != $pages) {

			// If the last page printed is not the last page
			$html .= '<li>'.$this->more_page.'</li>';
		}
		if($now_on_page != $pages && $pages != 0) {

			// If this is not the last page print the next link
			$html .= '<li><input class="nav-pages-bimage" type="image" src="'.$this->symbol['next']['src'].'" '
						.'id="'.$this->var_name.'_'.($now_on_page + 1).'" '
						.'name="'.$this->var_name.'['.($now_on_page + 1).']" '
						.'alt="'.$this->symbol['next']['alt'].'" /></li>';
		}
		if($cursor != $pages) {

			// If this is not the last page available print the end link
			$html .= '<li><input class="nav-pages-bimage" type="image" src="'.$this->symbol['end']['src'].'" '
						.'id="'.$this->var_name.'_'.$pages.'" '
						.'name="'.$this->var_name.'['.$pages.']" '
						.'alt="'.$this->symbol['end']['alt'].'" /></li>';
		}
		$html .= '</ul>';
		$html .= '<div class="nofloat"></div>'.'</div>';
		return $html;
		*/
	}

	function asSelected($var_name = false, $kind_of = false) {

		if($kind_of === false) $kind_of = $this->kind_of;
		if($var_name === false) $var_name = $this->var_name;
		$page = 1;
		if($kind_of == 'link') {

			if(isset($_GET[$var_name])) return true;
			else return false;
		} else {

			if(isset($_POST[$var_name])) return true;
			else return false;
		}
	}

	function getSelectedPage($var_name = false, $kind_of = false) {

		if($kind_of === false) $kind_of = $this->kind_of;
		if($var_name === false) $var_name = $this->var_name;
		$page = 1;
		if($kind_of == 'link') {

			if(isset($_GET[$var_name])) $page = $_GET[$var_name];
			else $page = 1;
		} else {

			if(isset($_POST[$var_name])) list($page) = each($_POST[$var_name]);
			else $page = 1;
		}
		return $page;
	}

	function getSelectedElement($var_name = false, $kind_of = false) {

		$page = NavBar::getSelectedPage($var_name, $kind_of);
		return ($page -1) * $this->element_per_page;
	}
}

?>