<?php

    /* Inizializzazione Forma*/
    chdir(dirname(__FILE__)); 
    define("IN_FORMA", true);
    define("_deeppath_", '');
    require( dirname(__FILE__).'/../base.php');
    require(_lib_ . '/lib.bootstrap.php');

    /*  alcune costanti */
    define('_output_path_', _files_.'/common/iofiles');
    define("_separatore_", ";");


    Boot::init(BOOT_PAGE_WR);

    try
    {
        recursiveDeleteOrgChart();
    }
    catch (Exception $e) {
        die ($e->getMessage());
    }



    function recursiveDeleteOrgChart($idParent = 0) {

        $acl =& Docebo::user()->getACLManager();
        $nodesArr = getPathsFromIdParent($idParent); // getting nodes with idParent
        $countNodes = count($nodesArr);

        if ($countNodes > 0) { // if there are any childs

            $node = 0;
            while ($node < $countNodes) { // Processing all nodes with idParent

                while (!$nodesArr[$node]['isLeaf']) // Getting last leaf
                    $nodesArr[$node]['isLeaf'] = recursiveDeleteOrgChart($nodesArr[$node]['idOrg']);

                // Deleting node

                //$query = "DELETE FROM " . $GLOBALS['prefix_fw'] . "_org_chart_tree WHERE iLeft>=".$nodesArr[$node]['iLeft']." AND iRight<=" .$nodesArr[$node]['iRight'];
                $query = "DELETE FROM " . $GLOBALS['prefix_fw'] . "_org_chart_tree WHERE idOrg=" . $nodesArr[$node]['idOrg'];
                $res = sql_query($query);

                $query = "DELETE FROM " . $GLOBALS['prefix_fw'] . "_org_chart WHERE id_dir = " . $nodesArr[$node]['idOrg'];
                $res = sql_query($query);
                if ($res){

                    $res = $acl->deleteGroup($acl->getGroupST('/oc_'.$nodesArr[$node]['idOrg']));
                    $res = $acl->deleteGroup($acl->getGroupST('/ocd_'.$nodesArr[$node]['idOrg']));

                }

                $node++;

            }

            return true;

        }
    }
    
    
    function getPathsFromIdParent($idParent) {
        $q = "SELECT path,idOrg,lev, iLeft, iRight FROM " . $GLOBALS['prefix_fw'] . "_org_chart_tree"
        . " WHERE idParent = ". $idParent ."";

        $rs = sql_query($q);
        $i = 0;
        while($rows = sql_fetch_array($rs)) {

            $nodesArr[$i]['text'] = $rows['path'];
            $nodesArr[$i]['idOrg'] = $rows['idOrg'];
            $nodesArr[$i]['level'] = $rows['lev'];
            $nodesArr[$i]['iLeft'] = $rows['iLeft'];
            $nodesArr[$i]['iRight'] = $rows['iRight'];

            // If the node has no child, the property will be NULL, otherwise will be an array to fill

            $nodesArr[$i]['isLeaf'] = ($rows['iRight'] - $rows['iLeft'] === 1);
            // $nodesArr[$i]['nodes'] = ($rows['iRight'] - $rows['iLeft'] === 1) ? NULL : array();

            $i++;
        }
        return $nodesArr;
    }
