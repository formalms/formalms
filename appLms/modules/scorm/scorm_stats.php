<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

/**
 * @module scorm_stats.php
 * @version $Id: scorm_stats.php 113 2006-03-08 18:08:42Z ema $
 * @copyright 2004 
 * @author Emanuele Sandri
 **/
/**
 *	This function print statistic about a user for a scorm organization
 *	@param int $idscorm_organization id of the organization
 *  @param int $idUser id of the user
 *	@return string to output
 **/
function scorm_userstat( $idscorm_organization, $idUser, $idReference = NULL, $mvc = false ) {
	require_once(dirname(__FILE__) . '/scorm_items_track.php');
	require_once(dirname(__FILE__) . '/CPManagerDb.php');
	require_once(dirname(__FILE__) . '/RendererBase.php');
	
	// get idscorm_package	
	$query = "SELECT idscorm_package, org_identifier "
			." FROM %lms_scorm_organizations"
			." WHERE idscorm_organization = '".$idscorm_organization."'";
	$rs = sql_query($query) 
			or communicationError( "3" );
	list( $idscorm_package, $org_identifier ) = sql_fetch_row($rs);

	$it = new Scorm_ItemsTrack( $GLOBALS['dbConn'], $GLOBALS['prefix_lms'] );
	$org_info = $it->getItemsInfo( $idReference, NULL, $idscorm_organization );

	$output = "";
	$str = "<br />".(!$mvc ? "<div class=\"std_block\">" : "");
	if ($mvc) {
		$output .= $str;
	} else {
		$GLOBALS['page']->add($str, 'content');
	}

	$cpm = new CPManagerDb();
	$cpm->Open($idReference , $idscorm_package, $GLOBALS['dbConn'], $GLOBALS['prefix_lms'] );
	$cpm->ParseManifest();
	$rb = new RendererDefaultImplementation();
	$rb->imgPrefix = getPathImage() . 'treeview/';
	$rb->imgOptions = 'width="24" height="24"';
	$rb->showlinks = FALSE;
	$rb->showit = TRUE;
	$rb->itemtrack = $it;
	$rb->idUser = $idUser;
	$rb->resBase = "";
	if( function_exists( 'cbMakeReportLink' ) )
		$rb->linkCustomCallBack = 'cbMakeReportLink';
	//$rb->renderStatusCallBack = "renderStatus";

	$str = $cpm->RenderOrganization( $org_identifier, $rb );
	if ($mvc) {
		$output .= $str;
	} else {
		$GLOBALS['page']->add($str, 'content');
	}
	if (!$mvc) $GLOBALS['page']->add("</div>", 'content');
	if ($mvc) return $output;
}


function scorm_userstat_detail( $idscorm_organization, $idUser, $idItem ) {
	require_once(_base_.'/lib/lib.domxml.php');
	require_once(dirname(__FILE__) . '/scorm_tracking.php');
		
	// get idscorm_package	
	$query = "SELECT idscorm_package"
			." FROM ".$GLOBALS['prefix_lms']."_scorm_organizations"
			." WHERE idscorm_organization = '".$idscorm_organization."'";
	$rs = sql_query($query) 
			or communicationError( "3" );
	list( $idscorm_package ) = sql_fetch_row($rs);
	
	
	$track = new Scorm_Tracking( $idUser, NULL, $idItem, $idscorm_package, $GLOBALS['dbConn'], false, true );
	
	$GLOBALS['page']->add("<br /><div class=\"std_block\">");
	$xmldoc = $track->getXmlDoc();

	$xpath = new DDOMXPath($xmldoc);
	
	$stack = array( FALSE );
	
	// status
	$nodeset = $xpath->query('//cmi/core/lesson_status/text()');	
	for($i = 0; $i < $nodeset->getLength(); $i++ ) {
		$GLOBALS['page']->add(render_node_row($nodeset->item($i),1,$stack,'Status'));
	}
	
	// total time
	$nodeset = $xpath->query('//cmi/core/total_time/text()');
	for($i = 0; $i < $nodeset->getLength(); $i++ )
		$GLOBALS['page']->add(render_node_row($nodeset->item($i),1,$stack,'Total time'));

	// score
	$nodeset = $xpath->query('//cmi/core/score/raw/text()');
	if( $nodeset->getLength() > 0 ) {
		$GLOBALS['page']->add(render_half_row(1,$stack,'Score',FALSE));
	
		$stack[1] = FALSE;
		
		$nodeset = $xpath->query('//cmi/core/score/raw/text()');
		for($i = 0; $i < $nodeset->getLength(); $i++ )
			$GLOBALS['page']->add(render_node_row($nodeset->item($i),2,$stack,'Raw'));
		$nodeset = $xpath->query('//cmi/core/score/min/text()');
		for($i = 0; $i < $nodeset->getLength(); $i++ )
			$GLOBALS['page']->add(render_node_row($nodeset->item($i),2,$stack,'Min'));
		$nodeset = $xpath->query('//cmi/core/score/max/text()');
		for($i = 0; $i < $nodeset->getLength(); $i++ )
			$GLOBALS['page']->add(render_node_row($nodeset->item($i),2,$stack,'Max',TRUE));
	}	
	$interactions = $xpath->query('//cmi/interactions');
	for($ci = 0; $ci < $interactions->getLength(); $ci++ ) {
		$ainteraction = $interactions->item($ci);
		$indexInteraction = $ainteraction->getAttribute('index');
		$GLOBALS['page']->add(render_half_row(1,$stack,'Interaction '.$indexInteraction,FALSE));
		
		//$nodeset = $xpath->xpath_eval('//cmi/interactions[@index='.$indexInteraction.']/id/text()');
		//foreach($nodeset->nodeset as $anode)
		//	$GLOBALS['page']->add(render_node_row($anode,2,$stack,'Id',FALSE));
		
		$nodeset = $xpath->query('//cmi/interactions[@index='.$indexInteraction.']/type/text()');
		for($i = 0; $i < $nodeset->getLength(); $i++ )
			$GLOBALS['page']->add(render_node_row($nodeset->item($i),2,$stack,'Type',FALSE));

		$nodeset = $xpath->query('//cmi/interactions[@index='.$indexInteraction.']/weighting/text()');
		for($i = 0; $i < $nodeset->getLength(); $i++ )
			$GLOBALS['page']->add(render_node_row($nodeset->item($i),2,$stack,'Weighting',FALSE));

		$nodeset = $xpath->query('//cmi/interactions[@index='.$indexInteraction.']/result/text()');
		for($i = 0; $i < $nodeset->getLength(); $i++ )
			$GLOBALS['page']->add(render_node_row($nodeset->item($i),2,$stack,'Result',TRUE));
	}
	
	
	//$GLOBALS['page']->add($xmldoc->dump_mem(true));
	$GLOBALS['page']->add("</div>");
}

function render_node_row($node, $deep, $stack, $label = NULL, $isLast = FALSE ) {
	if( $label === NULL )
		$label = $node->getTagName();
	$out = '<div class="report_on_tree">';
	$out .= '<span class="scorm_report_value">'.$node->getNodeValue().'</span>';
	for( $deepIndex = 0; $deepIndex < $deep; $deepIndex++ ) {
		$out .= '	<img class="TreeClass" src="'. scorm_stat_getImage( $deep, $deepIndex, $isLast, $stack[$deepIndex] )
  				.'" width="24px"/>'. "\n";
	}

	$out .= '<span class="scorm_report_label">'.$label.'</span>';
	$out .= '</div>';
	return $out;
}

function render_half_row( $deep, $stack, $label, $isLast = FALSE ) {
	$out = '<div class="report_on_tree">';

	$out .= '<span class="scorm_report_half_value"/></span>';
	for( $deepIndex = 0; $deepIndex < $deep; $deepIndex++ ) {
		$out .= '	<img class="TreeClass" src="'. scorm_stat_getImage( $deep, $deepIndex, $isLast, $stack[$deepIndex] )
  				.'" width="24px"/>'. "\n";
	}

	$out .= '<span class="scorm_report_half_label">'.$label.'</span>';
	$out .= '</div>';
	return $out;
}

function scorm_stat_getImage( $deep, $deepPos, $isLast, $isEnd ) {
    $imgLabel = '';
	require_once(dirname(__FILE__) . '/RendererBase.php');
	if( $deep == $deepPos ) {
		// handle REND_TITLE
		$imgLabel = SCORMREND_TITLE;
	} else if( $deep == $deepPos + 1 ) {
		// handle REND_EXPAND_INTER,REND_COLLAPSE_INTER,
		// REND_EXPAND_END,REND_COLLAPSE_END
		// REND_BRANCH_INTER,REND_BRANCH_END    // inLeaf
		if( $isLast ) {
            $imgLabel = SCORMREND_BRANCH_END;
		} else {
            $imgLabel = SCORMREND_BRANCH_INTER;
		}
	} else {
		// handle REND_VERT_INTER,REND_EMPTY
		if( $isEnd )
		    $imgLabel = SCORMREND_EMPTY;
		else
		    $imgLabel = SCORMREND_VERT_INTER;
	}
	
	return getPathImage() . 'treeview/' . $imgLabel;
}

?>
