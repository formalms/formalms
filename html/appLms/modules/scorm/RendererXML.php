<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

require_once Forma::inc(_lms_ . '/modules/scorm/RendererBase.php');

class RendererXML extends RendererAbstract
{
    public $xml;
    public $deep = 0;
    public $row = 0;
    public $stack;
    public $itemtrack;
    public $idUser;
    public $out = '';

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
    public function RenderStartItem($cpm, $itemInfo)
    {
        $out = '';			// string for ouput buffering
        // Add some info to $itemInfo hash array
        $itemInfo['isEnd'] = false;                 // the branch is not ended
        $itemInfo['idRow'] = $this->row;            // identifier of row
        $itemInfo['nChild'] = 0;    				// number of renderd child
        if ($this->deep > 0) {
            // increase the parent's number of childs
            ++$this->stack[$this->deep - 1]['nChild'];
            // set the sequence id. Progressive number in the set of siblings
            $itemInfo['idSeq'] = $this->stack[$this->deep - 1]['nChild'];
        } else {
            // set the sequence id. The root is always 1
            $itemInfo['idSeq'] = 1;
            $this->out = '';
        }

        // store $itemInfo in a stack (array) for next usage
        $this->stack[$this->deep] = $itemInfo;

        if ($this->deep == 0) {
            $rs = $this->itemtrack->getItemTrack($this->idUser, null, null, $itemInfo['uniqueid']);
        } else {
            $rs = $this->itemtrack->getItemTrack($this->idUser, null, $itemInfo['uniqueid'], null);
        }
        if ($rs === false) {
            $status = 'neverstarted';
        } else {
            $report = sql_fetch_assoc($rs);
            $status = $report['status'];
        }

        if ($itemInfo['isLeaf'] == '1') {
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
        $this->out .= '<item id="' . $itemInfo['identifier'] . '"'
                . ' isLeaf="' . $itemInfo['isLeaf'] . '"'
                . (isset($itemInfo['idscorm_package']) ? (' package="' . $itemInfo['idscorm_package'] . '"') : '')
                . (($itemInfo['identifierref']) ? (' identifierref="' . $itemInfo['identifierref'] . '"') : '')
                . ' prerequisites="' . $itemInfo['prerequisites'] . '"'
                . ' status="' . $status . '"'
                . ' uniqueid="' . $itemInfo['uniqueid'] . '"'
                . ' resource="' . $resInfo['uniqueid'] . '"'
                . '><title>' . htmlspecialchars($itemInfo['title'], ENT_QUOTES, 'UTF-8') . '</title>' . "\n";

        //echo '<br />'. "\n";
        // if this is the last child then the parent is ended
        if ($itemInfo['isLast'] && $this->deep > 0) {
            $this->stack[$this->deep - 1]['isEnd'] = true;
        }

        ++$this->deep;
        ++$this->row;
    }

    public function RenderStopItem($cpm, $itemInfo)
    {
        //echo '<!-- RenderStopItem ';
        //print_r($itemInfo);
        //echo '-->';
        $this->out .= '</item>';

        --$this->deep;
    }

    public function getOut()
    {
        return $this->out;
    }
}
