<?php

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
 * @module CPManager.php
 *
 * This module is the content package manager of the SpaghettiScorm
 *
 * @author Emanuele Sandri
 * @version $Id: RendererBase.php 113 2006-03-08 18:08:42Z ema $
 * @copyright 2004
 **/

/**
 *  RendererAbstract is the prototype class for all the
 *  Renderer objects
 */
class RendererAbstract {

	function RenderStartItem( $cpm, $itemInfo ){
		$cpm;
		$itemInfo;
	}
	
	function RenderStopItem( $cpm, $itemInfo ){
		$cpm;
		$itemInfo;
	}
}

/**
	|-----|-------------------------------------------------|
	|(0.a)|root (1.i)                                       |
	|-----|-----|-------------------------------------------|
	|(0.f)|(1.b)|child1 (2.i)                             	|
	|-----|-----|-----|-------------------------------------|
	|(0.f)|(1.c)|(2.d)|child1.1 (3.i)                       |
	|-----|-----|-----|-------------------------------------|
	|(0.f)|(1.c)|(2.e)|child1.2 (3.i)                       |
	|-----|-----|-----|-------------------------------------|
	|(0.f)|(1.a)|child2 (2.i)                               |
	|-----|-----|-----|-------------------------------------|
	|(0.f)|(1.h)|child3 (2.i)                               |
	|-----|-----|-----|-------------------------------------|
	|(0.f)|(1.f)|(2.h)|child3.1 (3.i)                       |
	|-----|-----|-----|-----|-------------------------------|
	|(0.f)|(1.f)|(2.f)|(3.e)|child3.1.1 (4.i)               |
	|-----|-----|-----|-----|-------------------------------|

	+   intermezzo	n.a
	-   intermezzo	n.b
	|   			n.c
	|-  			n.d
	L   			n.e
	    vuoto		n.f
	+   finale      n.g
	-   finale      n.h
	    titolo      n.i
	    
*/

/*define("REND_EXPAND_INTER", "a");
define("REND_COLLAPSE_INTER", "b");
define("REND_VERT_INTER", "c");
define("REND_BRANCH_INTER", "d");
define("REND_BRANCH_END", "e");
define("REND_EMPTY", "f");
define("REND_EXPAND_END", "g");
define("REND_COLLAPSE_END", "h");
define("REND_TITLE", "i");*/

define("SCORMREND_EXPAND_INTER", "menu_tee_plus.gif");
define("SCORMREND_COLLAPSE_INTER", "menu_tee_minus.gif");
define("SCORMREND_VERT_INTER", "menu_bar.gif");
define("SCORMREND_BRANCH_INTER", "menu_tee.gif");
define("SCORMREND_BRANCH_END", "menu_corner.gif");
define("SCORMREND_EMPTY", "menu_pixel.gif");
define("SCORMREND_EXPAND_END", "menu_corner_plus.gif");
define("SCORMREND_COLLAPSE_END", "menu_corner_minus.gif");
define("SCORMREND_TITLE", "menu_folder_open.gif");

class RendererDefaultImplementation extends RendererAbstract {

	// 6 class for any deep
	var $xClasses = array();
 	var $stack;
 	var $deep = 0;
	var $row = 0;
 	var $classPrefix = "ElemTree_";
	var $imgPrefix = "";
	var $imgOptions = "";
	var $resBase = "";
	var $showlinks = true;
	var $showit = false;
	var $itemtrack = null;
	var $idUser = FALSE;
	var $renderStatusCallBack = FALSE;
	var $linkCustomCallBack = FALSE;
	
	function RendererDefaultImplementation() {
        $this->xClasses[] = array( '0.a', '0.b', '0.c', '0.d', '0.e', '0.f', '0.g', '0.h', '0.i' );
	}

	function getRowClass() {
		return 'TreeRowClass';
	}

	/**
	 *  @param $deep actual deep
	 *  @param $deepPos position to get
	 *  @retun name of the stylesheet class
	 */
	function getClass( $deep, $deepPos ) {
	    $classIndex = $deepPos;
	    $classLabel = '';
		/* find correct position in xClasses array */
		if( $classIndex >= count( $this->xClasses ) )
		    $classIndex = count( $this->xClasses ) - 1;

		if( $deep == $deepPos ) {
			// handle REND_TITLE
			$classLabel = REND_TITLE;
		} else if( $deep == $deepPos + 1 ) {
			// handle REND_EXPAND_INTER,REND_COLLAPSE_INTER,
			// REND_EXPAND_END,REND_COLLAPSE_END
			// REND_BRANCH_INTER,REND_BRANCH_END    // inLeaf
			if( $this->stack[$deep]['isLeaf'] ) {
				if( $this->stack[$deep]['isLast'] ) {
                    $classLabel = REND_BRANCH_END;
				} else {
                    $classLabel = REND_BRANCH_INTER;
				}
			} else {
				if( $this->stack[$deep]['isLast'] ) {
                    $classLabel = REND_COLLAPSE_END;
				} else {
                    $classLabel = REND_COLLAPSE_INTER;
				}
			}
		} else {
			// handle REND_VERT_INTER,REND_EMPTY
			if( $this->stack[$deepPos]['isEnd'] )
			    $classLabel = REND_EMPTY;
			else
			    $classLabel = REND_VERT_INTER;
		}
		
		return $this->classPrefix . $classIndex . $classLabel;
	}

		/**
	 *  @param $deep actual deep
	 *  @param $deepPos position to get
	 *  @retun name of the stylesheet class
	 */
	function getImage( $deep, $deepPos ) {
	    $imgLabel = '';
	
		if( $deep == $deepPos ) {
			// handle REND_TITLE
			$imgLabel = SCORMREND_TITLE;
		} else if( $deep == $deepPos + 1 ) {
			// handle REND_EXPAND_INTER,REND_COLLAPSE_INTER,
			// REND_EXPAND_END,REND_COLLAPSE_END
			// REND_BRANCH_INTER,REND_BRANCH_END    // inLeaf
			if( $this->stack[$deep]['isLeaf'] ) {
				if( $this->stack[$deep]['isLast'] ) {
                    $imgLabel = ''; // SCORMREND_BRANCH_END;
				} else {
                    $imgLabel = ''; // SCORMREND_BRANCH_INTER;
				}
			} else {
				if( $this->stack[$deep]['isLast'] ) {
                    $imgLabel = ''; // SCORMREND_COLLAPSE_END;
				} else {
                    $imgLabel = ''; // SCORMREND_COLLAPSE_INTER;
				}
			}
		} else {
			// handle REND_VERT_INTER,REND_EMPTY
			if( $this->stack[$deepPos]['isEnd'] )
			    $imgLabel = SCORMREND_EMPTY;
			else
			    $imgLabel = ''; // SCORMREND_VERT_INTER;
		}
		return '';
		if($imgLabel == '') return $imgLabel;
		return $this->imgPrefix . $imgLabel;
	}

	/**
	 *  @param $cpm reference to CPManager
	 *  @param $itemInfo info array:
	 *      'identifier'
	 *      'isLast'
	 *      'identifierref'
	 *      'isvisible'
	 *      'parameters'
	 *      'title'
	 *      'isLeaf'
	 */
	function RenderStartItem( $cpm, $itemInfo ){
		$out = "";			// string for ouput buffering
		// Add some info to $itemInfo hash array
		$itemInfo['isEnd'] = FALSE;                 // the branch is not ended
		$itemInfo['idRow'] = $this->row;            // identifier of row
  		$itemInfo['nChild'] = 0;    				// number of renderd child
		if($this->deep > 0) {
            // increase the parent's number of childs
        	$this->stack[$this->deep-1]['nChild']++;
        	// set the sequence id. Progressive number in the set of siblings
        	$itemInfo['idSeq'] = $this->stack[$this->deep-1]['nChild'];
		} else {
            // set the sequence id. The root is always 1
            $itemInfo['idSeq'] = 1;
		}
		
		// store $itemInfo in a stack (array) for next usage
		$this->stack[$this->deep] = $itemInfo;
    $tadd="";

		if ($this->deep == 0)
			$rs = $this->itemtrack->getItemTrack($this->idUser, null, null, $itemInfo['uniqueid'] );
		else 
			$rs = $this->itemtrack->getItemTrack($this->idUser, null, $itemInfo['uniqueid'], null );
		if ( $rs === FALSE ) {
			if( $this->showit ) 
				$out .= "<div class=\"reportincomplete\">Never started</div>";
			else
				$text = "neverstarted"; 
		} else {
			$report = sql_fetch_assoc($rs);
			if( $this->renderStatusCallBack === FALSE ) {
				switch($report['status']) {
					case 'completed': {
						$class = "reportcomplete";
						$text = "completed";
					} break;
					case 'passed': {
						$class = "reportcomplete";
						$text = "passed";
            $tadd = ": ".$this->itemtrack->getTrackingScore($report['idscorm_tracking'])."/".$itemInfo['adlcp_masteryscore'];
					} break;
					case 'failed': {
						$class = "reportincomplete";
						$text = "failed";
            $tadd = ": ".$this->itemtrack->getTrackingScore($report['idscorm_tracking'])."/".$itemInfo['adlcp_masteryscore'];
					} break;
					default: {
						$class = "reportincomplete";
						$text = $report['status'];
					} break;
				}
				if( $this->showit ) {
					if( $this->linkCustomCallBack !== FALSE ) {
						$func = $this->linkCustomCallBack;
						$out .= "<div class=\"report_on_tree $class\">".$func(Lang::t($text, 'standard', 'framework').$tadd,$itemInfo['uniqueid'])."</div>";
					} else {
						$out .= "<div class=\"report_on_tree $class\">". Lang::t($text, 'standard', 'framework').$tadd."</div>";
					}
				}
			} else {
				$cback = $this->renderStatusCallBack;
				if( $report['status'] == 'passed' || $report['status'] == 'failed' )
					$cback($report['status'],$this->itemtrack->getTrackingScore($report['idscorm_tracking']),$itemInfo['adlcp_masteryscore']);
				else 
					$cback($report['status']);
			}
		}
		
		// start a new level
		// one block for any deep
		
  		$out .= '<span class="'
  				.( $itemInfo['identifierref'] 
  					? $this->getRowClass() 
  					: (  $this->deep != 0
  						? 'TreeRowFolder' 
  						: 'TreeRoot' ) 
  				).'"'
		  		.' id="';
  		for( $ideep = 0; $ideep < $this->deep; $ideep++ )
  		    $out .= $this->stack[$ideep]['idSeq'] . '.';
		$out .= $this->stack[$this->deep]['idSeq'];
		$out .= '">'. "\n";
		for( $deepIndex = 0; $deepIndex < $this->deep; $deepIndex++ ) {
			
			$img = $this->getImage( $this->deep, $deepIndex );
			if($img != '') $out .= '	<img class="TreeClass" src="'. $img .'" '. $this->imgOptions .' />'. "\n";
		}
			
		if( !$itemInfo['isLeaf'] ) {
			/*$out .= '	<img class="TreeClass" src="'. $this->getImage( $this->deep, $deepIndex )
					.'" '. $this->imgOptions .' />'. "\n";
					*/
		}
		
		if( $itemInfo['identifierref'] ) {
			$resInfo = $cpm->GetResourceInfo($itemInfo['identifierref']);
			if( $itemInfo['prerequisites']) {
				if( $this->showlinks ) {
					// Make link with LoadSco( this, idscorm_resource, idscorm_item, idscorm_organization, scormtype )
					if( $text == 'completed' || strncmp($text,'passed',6) == 0 ) {
						$out .= '<img class="icoCompleted" src="'.$this->imgPrefix.'completed.gif" '
								. $this->imgOptions
								.' alt="completed" title="completed" />';
						$out .= '<a href="#'.$text.'" id="vink'.$itemInfo['idRow'].'"';
					} elseif( $text == 'incomplete' ) {
						$out .= '<img class="icoCompleted" src="'.$this->imgPrefix.'incomplete.gif" '
								. $this->imgOptions
								.' alt="incomplete" title="incomplete" />';
						$out .= '<a href="#'.$text.'" id="link'.$itemInfo['idRow'].'"';						
					} else {
						$out .= '<a href="#'.$text.'" id="link'.$itemInfo['idRow'].'"';
					}
					$out .= ' class="accessible"'
							.' onclick="LoadSco(this,\''
							.$resInfo['uniqueid'].'\',\''
							.$itemInfo['uniqueid'].'\',\''
							.$this->stack[0]['uniqueid'].'\',\''
							.$resInfo['scormtype'] 
							.'\'); return false;">'
							.$itemInfo['title']
							.'</a>';
				} else {				
					if( $this->linkCustomCallBack !== FALSE ) {
						$func = $this->linkCustomCallBack;
						$out .= $func($itemInfo['title'],$itemInfo['uniqueid']);
					} else {
						$out .= $itemInfo['title'];
					}
				}
			} else {
				if( $this->showlinks ) {
					$out .= '<img class="icoCompleted" src="'.$this->imgPrefix.'loked.gif" '
							. $this->imgOptions
							.' alt="locked" title="locked" />';
					$out .= '<a href="#'.$text.'" id="noaccess'.$itemInfo['idRow'].'" class="notaccessible" onclick="alert(\'The prerequisites are not satisfied!\'); return false;">'
					.$itemInfo['title']
					.'</a>';
					/*$out .= '<span id="noaccess'.$itemInfo['idRow'].'" class="notaccessible">'
					.$itemInfo['title']
					.'</span>';*/
				} else {
					if( $this->linkCustomCallBack !== FALSE ) {
						$func = $this->linkCustomCallBack;
						$out .= $func($itemInfo['title'],$itemInfo['uniqueid']);
					} else {
						$out .= $itemInfo['title'];
					}			
				}
			}
		} else {
			$out .= '<span class="TextNode" id="treenode'.$itemInfo['idRow'].'">'.$itemInfo['title'].'</span>'."\n";
		}
		$out .= '</span>'. "\n";

		
		//echo '<br />'. "\n";
		// if this is the last child then the parent is ended
		if( $itemInfo['isLast'] && $this->deep > 0 )
		    $this->stack[$this->deep - 1]['isEnd'] = TRUE;

		$this->deep++;
		$this->row++;
		$GLOBALS['page']->add( $out );
	}

	function RenderStopItem( $cpm, $itemInfo ){
		//echo '<!-- RenderStopItem ';
		//print_r($itemInfo);
		//echo '-->';
		$cpm;
		$itemInfo;
		$this->deep--;
	}
	
}


?>
