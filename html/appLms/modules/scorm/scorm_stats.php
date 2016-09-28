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

// TODO: sourced from doceboLms\modules\organization\orgresults.php -- to be reviewed
function decodeSessionTime($stime) {
	$output = $stime;
	if (strpos($stime, 'P')!==false) {
		$re1 = preg_match ('/^P((\d*)Y)?((\d*)M)?((\d*)D)?(T((\d*)H)?((\d*)M)?((\d*)(\.(\d{1,2}))?S)?)?$/', $stime, $t1_s );
		if(!isset($t1_s[15]) || $t1_s[15] == '') $t1_s[15] = '00';
		if(!isset($t1_s[13]) || $t1_s[13] == '') $t1_s[13] = '00';
		if(!isset($t1_s[11]) || $t1_s[11] == '') $t1_s[11] = '00';
		if(!isset($t1_s[9]) || $t1_s[9] == '') $t1_s[9] = '0000';
		$output = ($t1_s[9]=='0000' || $t1_s[9] == '' ? '' : $t1_s[9].':')
			.sprintf("%'02s:%'02s.%'02s",  $t1_s[11], $t1_s[13], $t1_s[15]);
	}
	return $output;
}

// TODO: sourced from appLms/modules/organization/orgresults.php -- to be reviewed
function getTrackingTable($id_user, $id_org, $idscorm_item, $idReference) {

	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(Get::sett('visu_course'));

	$lang = DoceboLanguage::CreateInstance('organization', 'lms');

	$h_type = array('', '', 'image', 'image', '', 'nowrap', 'image', 'image nowrap');
	$h_content = array(
		$lang->def('_NAME'),
		$lang->def('_STATUS'),
		$lang->def('_SCORE'),
		$lang->def('_MAX_SCORE'),
		$lang->def('_DATE_LAST_ACCESS'),
		$lang->def('_TIME'),
		$lang->def('_ATTEMPTS'),
		''
	);

	$tb->setColsStyle($h_type);
	$tb->addHead($h_content);

	$query = "SELECT idscorm_item, status ".
		" FROM ".$GLOBALS['prefix_lms']."_scorm_items_track  ".
		" WHERE idscorm_organization=$id_org ".
		" AND idUser=$id_user ";
	$lessons_status = array();
	$res = sql_query($query);
	while (list($id, $s) = sql_fetch_row($res)) {
		$lessons_status[$id] = $s;
	}

	$qry = "SELECT t3.title, t1.lesson_status, t1.score_raw, t1.score_max, t1.session_time, t1.total_time, ".
		" MAX(t2.date_action) as last_access, COUNT(*) as attempts, t1.idscorm_item as item, t1.idscorm_tracking as id_track ".
		" FROM ".$GLOBALS['prefix_lms']."_scorm_tracking as t1, ".
		" ".$GLOBALS['prefix_lms']."_scorm_tracking_history as t2, ".
		" ".$GLOBALS['prefix_lms']."_scorm_items as t3 ".
		" WHERE t1.idscorm_item=t3.idscorm_item AND ".
		" t2.idscorm_tracking=t1.idscorm_tracking AND t3.idscorm_organization=$id_org ".
		" AND t1.idUser=$id_user AND t1.idscorm_item=$idscorm_item ".
		" GROUP BY t2.idscorm_tracking";

	$res = sql_query($qry);
	while ($row = sql_fetch_assoc($res)) {

		$line = array();

		$interactions = '<a href="index.php?modname=stats&op=statoneuseroneiteminteractions&amp;id_user='.$id_user.'&amp;idItem='.$idReference.'&amp;id_track='.$row['id_track'].'">'.$lang->def('_SHOW_INTERACTIONS').'</a>';
        $scorm_history = '<a href="index.php?modname=stats&op=statoneuseroneitemhistory&amp;idUser='.$id_user.'&amp;idItem='.$idReference.'&amp;idItemDetail='.$row['item'].'&amp;backto=statoneuseroneitem">'.$lang->def('_HISTORY').'</a>';

		$line[] = $row['title'];
		$line[] = $lessons_status[$row['item']];
		$line[] = $row['score_raw'];
		$line[] = $row['score_max'];
		$line[] = Format::date($row['last_access']);
		$line[] = decodeSessionTime($row['total_time']);
		$line[] = $row['attempts'];
		//$line[] = ($row['score_raw']!='' ? $interactions : '');
		$line[] = ( $row['attempts'] > 1 ? $scorm_history : '' );
//			.($row['score_raw']!='' ? '<br />'.$interactions : '');

		$tb->addBody($line);

	}
	cout( $tb->getTable(), 'content' );

} //end function


// TODO: sourced from appLms/modules/organization/orgresults.php -- to be reviewed
function getHistoryTable($id_user, $id_org, $idscorm_item, $idReference) {

	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(Get::sett('visu_course'));

	$lang = DoceboLanguage::CreateInstance('organization', 'lms');

	$h_type = array('', '', '', '', '');
	$h_content = array(
		$lang->def('_ATTEMPT'),
		$lang->def('_STATUS'),
		$lang->def('_SCORE'),
		$lang->def('_DATE'),
		$lang->def('_TIME')
	);

	$tb->setColsStyle($h_type);
	$tb->addHead($h_content);

	$qry = "SELECT t1.* FROM ".
		$GLOBALS['prefix_lms']."_scorm_tracking_history as t1 JOIN ".
		$GLOBALS['prefix_lms']."_scorm_tracking as t2 ON (t1.idscorm_tracking=t2.idscorm_tracking) ".
		" WHERE t2.idscorm_item=$idscorm_item AND t2.idUser=$id_user ".
		" ORDER BY t1.date_action ASC ";
	$res = sql_query($qry); $i=1;
	while ($row = sql_fetch_assoc($res)) {

		$line = array();

		$line[] = $lang->def('_ATTEMPT').' '.$i;
		$line[] = $row['lesson_status'];
		$line[] = $row['score_raw'];
		$line[] = Format::date($row['date_action']);
		$line[] = decodeSessionTime($row['session_time']);

		$tb->addBody($line);
		$i++;
	}

	//title
	cout( $tb->getTable(), 'content' );

}

function scorm_userstat_detailhist( $idscorm_organization, $idUser, $idItem, $idReference ) {
	return( getHistoryTable($idUser, $idscorm_organization, $idItem, $idReference ) );
}

function scorm_userstat_detail( $idscorm_organization, $idUser, $idItem, $idReference ) {

	return( getTrackingTable($idUser, $idscorm_organization, $idItem, $idReference ) );

/*** disabled ****  // XML SCORM results

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

**** disabled ***/  // XML SCORM results

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
