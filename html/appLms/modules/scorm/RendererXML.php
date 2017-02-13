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

require_once(dirname(__FILE__) . '/RendererBase.php');

class RendererXML extends RendererAbstract {

	var $xml;
	var $deep = 0;
	var $row = 0;
	var $stack;
	var $itemtrack;
	var $idUser;
	var $out = '';
	
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
            $this->out = '';
		}
		
		// store $itemInfo in a stack (array) for next usage
		$this->stack[$this->deep] = $itemInfo;
		
		if ($this->deep == 0)
			$rs = $this->itemtrack->getItemTrack($this->idUser, null, null, $itemInfo['uniqueid'] );
		else 
			$rs = $this->itemtrack->getItemTrack($this->idUser, null, $itemInfo['uniqueid'], null );
		if ( $rs === FALSE ) {
			$status = "neverstarted"; 
		} else {
			$report = sql_fetch_assoc($rs);
			$status = $report['status'];
		}
		
		if( $itemInfo['isLeaf'] == '1' ) {
			$resInfo = $cpm->GetResourceInfo($itemInfo['identifierref']);
		} else {
			$resInfo['href'] = '';
			$resInfo['identifier'] = '';
			$resInfo['type'] = '';
			$resInfo['scormtype'] = '';
			$resInfo['uniqueid'] = '';
		}
		
		// start a new level
		// one block for any deep
  		$this->out .= '<item id="'.$itemInfo['identifier'].'"'
				.' isLeaf="'.$itemInfo['isLeaf'].'"'
				.(isSet($itemInfo['idscorm_package'])?(' package="'.$itemInfo['idscorm_package'].'"'):'')
				.(($itemInfo['identifierref'])?(' identifierref="'.$itemInfo['identifierref'].'"'):'')
				.' prerequisites="'.$itemInfo['prerequisites'].'"'
				.' status="'.$status.'"'
				.' uniqueid="'.$itemInfo['uniqueid'].'"'
				.' resource="'.$resInfo['uniqueid'].'"'
				.'><title>'.htmlspecialchars($itemInfo['title'], ENT_QUOTES, 'UTF-8').'</title>'."\n";
		
		//echo '<br />'. "\n";
		// if this is the last child then the parent is ended
		if( $itemInfo['isLast'] && $this->deep > 0 )
		    $this->stack[$this->deep - 1]['isEnd'] = TRUE;

		$this->deep++;
		$this->row++;
	}

	function RenderStopItem( $cpm, $itemInfo ){
		//echo '<!-- RenderStopItem ';
		//print_r($itemInfo);
		//echo '-->';
		$this->out .= '</item>';
		$cpm;
		$itemInfo;
		$this->deep--;
	}
	
	function getOut() {
		return $this->out;
	}
	
}
?>