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
 * @module scorm_page_head.php
 *
 * @version $Id: scorm_page_head.php 229 2006-04-10 11:35:05Z ema $
 * @copyright 2003 
 **/
 
ob_end_clean();
ob_start();


if(!Docebo::user()->isLoggedIn() || !isset($_SESSION['idCourse'])) 
	die( "Malformed request" ); 

require_once(dirname(__FILE__) . '/config.scorm.php');


@sql_query("SET NAMES '".$GLOBALS['db_conn_names']."'", $dbconn);
@sql_query("SET CHARACTER SET '".$GLOBALS['db_conn_char_set']."'", $dbconn);

require_once(dirname(__FILE__) . '/scorm_items_track.php');

$idscorm_organization = (int)$_GET['idscorm_organization'];
$idReference = (int)$_GET['idReference'];
$imagesPath = getPathImage().'scorm/';
$playertemplate = ( isset($_GET['template']) ? $_GET['template'] : '' );

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 Strict//EN"';
echo '    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml">';
echo '<head>';
echo '	<title>Untitled document</title>';
// TODO: verificare se la prossima riga � un problema con IIS
// echo '	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />';
echo '	<link href="'.getPathTemplate().'style/base.css" rel="stylesheet" type="text/css" />';
echo '	<link href="'.getPathTemplate().'style/lms-scormplayer.css" rel="stylesheet" type="text/css" />';
if($playertemplate != '') {
	if(file_exists(getPathTemplate().'player_scorm/'.$playertemplate.'/def_style.css'))
		echo '	<link href="'.getPathTemplate().'player_scorm/'.$playertemplate.'/def_style.css" rel="stylesheet" type="text/css" />';
	else $playertemplate = '';
}

echo <<<_TEXTEND
<script type="text/javascript" language="JavaScript">
	<!--


	var API = window.top.API;
	
	function FinishCallBack( param ) {
		window.location.reload();
	}
	
	function Initialize() {
		if( window.top.API != null ) 
			FindApiScorm();
		else
			window.setTimeout(FindApiScorm, 500);
		InitNav();		
	}
	
	function InitNav() {
		if( window.top.frames['scormtree'] == null ) {
			window.setTimeout("InitNav()", 200);
		}
		if( window.top.frames['scormtree'].isLoaded )
			window.top.frames['scormtree'].SetHeaderPrevNext();
		else 
			window.setTimeout("InitNav()", 200);
	}
	
	function FindApiScorm() {
		if( window.top.API != null ) {
			API = window.top.API;
			// API.dbgOut = window.document.getElementById("dbgWindow");
		} else
			window.setTimeout(FindApiScorm, 500);
	}
		
	function hideTree() {
		window.top.hideTree();
	}
	function showTree() {
		window.top.showTree();
	}
	function setTreeVisibility( isVisible ) {
		if( isVisible ) {
			document.getElementById('hidetree').style.display = 'inline';
			document.getElementById('showtree').style.display = 'none';
		} else {
			document.getElementById('hidetree').style.display = 'none';
			document.getElementById('showtree').style.display = 'inline';
		}
	}
	
	function closeScormRTE() {
		window.top.goBack();
	}	
	function navPrev() {
		var prevElem = window.document.getElementById('prevlink');
		if( prevElem['elemLink'] != null ) {
			prevElem.elemLink.onclick();
		}
	}		
	function navNext() {
		var nextElem = window.document.getElementById('nextlink');
		if( nextElem['elemLink'] != null ) {
			nextElem.elemLink.onclick();
		}
	}	
	
	window.setPreviousSCO = function ( obj ) {
		var blockelem = window.document.getElementById('prevblocklink');
		var prevElem = window.document.getElementById('prevlink');
/*		var textElem = prevElem.firstChild;
		if( document.all ) { // IE 
			if( textElem != null && textElem.nodeType == 3 ) {
				prevElem.removeChild(textElem);
			}
		} else {
			if( textElem != null && textElem.nodeType == textElem.TEXT_NODE ) {
				prevElem.removeChild(textElem);
			}
		}*/
		if( obj != null ) {
			//textElem = window.document.createTextNode(obj.title);
			//prevElem.appendChild(textElem);
			document.getElementById('imgprev').title = obj.title;
			prevElem['elemLink'] = obj.elem;
			blockelem.style.visibility = 'visible';
		} else {
			blockelem.style.visibility = 'hidden';
		}
		prevElem['linkObj'] = obj;
	}
	
	window.setNextSCO = function ( obj ) {
		var blockelem = window.document.getElementById('nextblocklink');
		var nextElem = window.document.getElementById('nextlink');
/*		var textElem = nextElem.firstChild;
		if( document.all ) { // IE 
			if( textElem != null && textElem.nodeType == 3 ) {
				nextElem.removeChild(textElem);
			}
		} else {
			if( textElem != null && textElem.nodeType == textElem.TEXT_NODE ) {
				nextElem.removeChild(textElem);
			}
		}*/
			
		if( obj != null ) {
			//textElem = window.document.createTextNode(obj.title);
			//nextElem.appendChild(textElem);
			document.getElementById('imgnext').title = obj.title;
			nextElem['elemLink'] = obj.elem;
			blockelem.style.visibility = 'visible';
		} else {
			blockelem.style.visibility = 'hidden';
		}
		nextElem['linkObj'] = obj;
	}
	// -->
	</script>
</head>
_TEXTEND;

echo '<body id="page_head" class="'.( $playertemplate != '' ? $playertemplate.'_back' : 'standard_back' ).'" onload="Initialize()">';

echo "<div id=\"headnav\">\n"
	."	<div>\n"
	."		<div id=\"prevblocklink\" style=\"display:block; float: left;\">\n"
	."			<A id=\"prevsco\" href=\"#\" onClick=\"navPrev(); return false;\"><img class=\"imgnav\" id=\"imgprev\" src=\"".$imagesPath."bt_sx.png\" alt=\"Back\" /></A>\n"
	."			<span id=\"prevlink\"></span>"
	."		</div>\n"
	."		<div id=\"nextblocklink\" style=\"display:block; float: left;\">\n"
	."			<A id=\"nextsco\" href=\"#\" onClick=\"navNext(); return false;\"><img class=\"imgnav\" id=\"imgnext\" src=\"".$imagesPath."bt_dx.png\" alt=\"Next\" /></A>\n"
	."			<span id=\"nextlink\"></span>\n"
	."		</div>\n"	
	."		<script type=\"text/javascript\">\n"
	."		<!--\n"
	."			if(window.top.isShowTree()) {\n"
	."				document.write('<a id=\"hidetree\" style=\"display:inline\" href=\"#\" onClick=\"hideTree(); return false;\"><img class=\"imgnav\" src=\"".$imagesPath."hide_tree.png\" alt=\"Hide Tree\" /></a>');\n"
	."				document.write('<a id=\"showtree\" style=\"display:none\" href=\"#\" onClick=\"showTree(); return false;\"><img class=\"imgnav\" src=\"".$imagesPath."show_tree.png\" alt=\"Show Tree\" /></a>');\n"
	."			} else {\n"
	."				document.write('<a id=\"hidetree\" style=\"display:none\" href=\"#\" onClick=\"hideTree(); return false;\"><img class=\"imgnav\" src=\"".$imagesPath."hide_tree.png\" alt=\"Hide Tree\" /></a>');\n"
	."				document.write('<a id=\"showtree\" style=\"display:inline\" href=\"#\" onClick=\"showTree(); return false;\"><img class=\"imgnav\" src=\"".$imagesPath."show_tree.png\" alt=\"Show Tree\" /></a>');\n"
	."			}\n"
	."		-->\n"
	."		</script>\n"
	."	</div>\n"
	."</div>\n";

/*echo '<div id="headerLogo">';
echo '<img src="'.$imagesPath.'logo.jpg" alt="logo spaghettilearning scorm" />';
echo '</div>';*/
		//echo '<div class="header">'."\n"
		//	."\t".'<img class="immagineSx logo_sx" src="'.getPathImage().'scorm/logo.png" alt="Left logo" />'."\n";

//echo "<img id=\"immagineSx\" src=\"../../templates/".$_SESSION["sesTemplate"]."/images/scorm/logo.png\" alt=\"Logo\" />\n";

// statistics
echo '<div id="statistics">';
$itemtrack = new Scorm_ItemsTrack($dbconn, $GLOBALS['prefix_lms']);
$rs = $itemtrack->getItemTrack(sl_sal_getUserId(), $idReference, NULL, $idscorm_organization);
if( $rs === FALSE ) {
	echo "Lesson never initiated";
} else {
	$report = sql_fetch_assoc($rs);
	echo Lang::t('_PROGRESS', 'scorm')." ".$report['nDescendantCompleted']."/".$report['nDescendant']."<br />";
	$widthMax = 220;
	$widthOne = ($widthMax-$report['nDescendant'])/$report['nDescendant'];
	$posRel = 2;
	echo '<div class="scorm_progressbarstat">';
	//echo "<div >";
	for($nRep = 0; $nRep < $report['nDescendantCompleted']; $nRep++, $posRel += $widthOne+1 ) {
		echo "<div class=\"scorm_complete\" style=\"width: ".$widthOne."px; left:".$posRel."px; top: 2px;\"></div>";
		//echo "<div class=\"scorm_complete\" style=\"width: ".$widthOne."%;\" >&nbsp;</div>";
	}
	for(; $nRep < $report['nDescendant']; $nRep++, $posRel += $widthOne+1 ) {
		echo "<div class=\"scorm_incomplete\" style=\"width: ".$widthOne."px; left:".$posRel."px; top: 2px;\"></div>";
		//echo "<div class=\"scorm_incomplete\" style=\"width: ".$widthOne."%;\" >&nbsp;</div>";
	}
	//echo '<div style="float: left; height: 14px; border-left: 1px solid black;" >&nbsp;</div>';
	//echo '<div class="noFloat" style="border-left: 1px solid black;" ></div>';
	echo "</div>";
	
}
echo '</div>';

echo '<div id="headtitle">'
	.'<script type="text/javascript">'
	.'document.write(window.top.getTitle());'
	.'</script>'
	.'</div>';

echo "<a id=\"closewindow\" href=\"#\" onclick=\"closeScormRTE(); return false;\" ><img class=\"imgnav\" src=\"".$imagesPath."bt_exit.gif\" alt=\"Close\" /></A>\n";

echo "</div>\n"
	."</body>\n"
	."</html>\n";
	
ob_end_flush();
exit;	// to avoid index.php to add additional and unuseful html

?>
