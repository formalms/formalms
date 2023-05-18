<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/*
define("LMS", true);
define("IN_FORMA", true);
define("_deeppath_", '../../../');
//require(dirname(__FILE__).'/../base.php');

// start buffer
ob_start();

// initialize
require_once(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_TEMPLATE);
*/
if (Forma::user()->isLoggedIn()) {
    require_once Forma::inc(_lms_ . '/modules/scorm/config.scorm.php');
    require_once Forma::inc(_lms_ . '/modules/scorm/scorm_utils.php');
    require_once Forma::inc(_lms_ . '/modules/scorm/scorm_items_track.php');

    $idReference = $GLOBALS['idReference'];
    $idResource = $GLOBALS['idResource'];
    $back_url = $GLOBALS['back_url'];
    $autoplay = $GLOBALS['autoplay'];
    $playertemplate = $GLOBALS['playertemplate'];
    $environment = $GLOBALS['environment'];
    if (!empty($GLOBALS['chapter'])) {
        $start_from_chapter = $GLOBALS['chapter'];
    } else {
        $start_from_chapter = FormaLms\lib\Get::req('start_from_chapter', DOTY_MIXED, false);
    }

    if ($autoplay == '') {
        $autoplay = '1';
    }
    if ($playertemplate == '') {
        $playertemplate = 'default';
    }
    if ($environment == false) {
        $environment = 'course_lo';
    }

    if ($playertemplate != '') {
        if (!file_exists(getPathTemplate() . 'player_scorm/' . $playertemplate . '/def_style.css')) {
            $playertemplate = 'default';
        }
    } else {
        $playertemplate = 'default';
    }

    switch ($environment) {
    case 'communication':
        list($idscorm_organization, $lo_title) = sql_fetch_row(sql_query('SELECT idscorm_organization,title FROM %lms_scorm_organizations where idscorm_package=' . $idResource));
        break;
    default:
        $idscorm_organization = $idResource;
        list($lo_title) = sql_fetch_row(sql_query('SELECT title'
            . ' FROM %lms_organization'
            . " WHERE idResource = '$idResource'"
            . "   AND objectType = 'scormorg'"));
        break;
}

    $idUser = (int) getLogUserId();

    /*Start database connection***********************************************/

    /* get scorm version */
    $scormVersion = getScormVersion('idscorm_organization', $idscorm_organization);

    /* get object title */

    $itemtrack = new Scorm_ItemsTrack(null, $GLOBALS['prefix_lms']);
    $rsItemTrack = $itemtrack->getItemTrack($idUser, $idReference, null, $idscorm_organization);
    if ($rsItemTrack === false) {
        // The first time for this user in this organization
        $itemtrack->createItemsTrack($idUser, $idReference, $idscorm_organization);
        // Now should be present
        $rsItemTrack = $itemtrack->getItemTrack($idUser, $idReference, null, $idscorm_organization);
    }

    $arrItemTrack = sql_fetch_assoc($rsItemTrack);
    // with id_item_track of organization|user|reference create an entry in commontrack table
    require_once Forma::inc(_lms_ . '/class.module/track.object.php');
    require_once Forma::inc(_lms_ . '/class.module/track.scorm.php');
    $track_so = new Track_ScormOrg($arrItemTrack['idscorm_item_track'], false, false, null, $environment);
    if ($track_so->idReference === null) {
        $track_so->createTrack($idReference, $arrItemTrack['idscorm_item_track'], $idUser, date('Y-m-d H:i:s'), 'ab-initio', 'scormorg');
    }

    /* info on number of items and setting of variables for tree hide/show */
    $nItem = $arrItemTrack['nDescendant'];
    if (!empty($GLOBALS['chapter'])) {
        $isshow_tree = 'false';
        $class_extension = '_hiddentree';
    } else {
        $isshow_tree = $nItem > 1 ? 'true' : 'false';
        $class_extension = $nItem > 1 ? '' : '_hiddentree';
    }

    $lms_base_url = FormaLms\lib\Get::abs_path();

    $lms_url = $lms_base_url . $scormws;
    $xmlTreeUrl = $lms_base_url . $scormxmltree . '?idscorm_organization=' . $idscorm_organization . '&idReference=' . $idReference . '&environment=' . $environment;
    $imagesPath = getPathImage() . 'treeview/';

    // support for setting keepalive tmo
$gc_maxlifetime = ini_get('session.gc_maxlifetime');	// seconds
$cfg_keepalivetmo = FormaLms\lib\Get::cfg('keepalivetmo', 0);	// minumum : 60 sec.

if ($cfg_keepalivetmo > 0) {
    $keepalivetmo = $cfg_keepalivetmo;
} else {
    if ($gc_maxlifetime > 15 * 60) {
        $keepalivetmo = 14 * 60;
    } else {
        if ($gc_maxlifetime >= 2 * 60) {
            $keepalivetmo = $gc_maxlifetime - 60;
        } else {
            $keepalivetmo = $gc_maxlifetime - 15;    // second
        }
    }
}

    header('Content-Type: text/html; charset=utf-8');

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 Strict//EN"' . "\n";
    echo '    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
    echo '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
    echo '<head>';
    echo '	<title>' . $lo_title . '</title>';
    echo '	<link href="' . FormaLms\lib\Get::tmpl_path() . '/style/lms-scormplayer.css" rel="stylesheet" type="text/css" />';

    if (trim($playertemplate) != '') {
        echo '	<link href="' . FormaLms\lib\Get::tmpl_path() . '/player_scorm/' . $playertemplate . '/def_style.css" rel="stylesheet" type="text/css" />';
    }

    $rnd = 2007101900;
    echo '<SCRIPT type="text/javascript" src="' . FormaLms\lib\Get::rel_path('lms') . '/modules/scorm/prototype.js' . "?v=$rnd" . '"></SCRIPT>' . "\n";
    echo '<SCRIPT type="text/javascript" src="' . FormaLms\lib\Get::rel_path('lms') . '/modules/scorm/ScormTypes.js' . "?v=$rnd" . '"></SCRIPT>' . "\n";
    echo '<SCRIPT type="text/javascript" src="' . FormaLms\lib\Get::rel_path('lms') . '/modules/scorm/ScormCache.js' . "?v=$rnd" . '"></SCRIPT>' . "\n";
    echo '<SCRIPT type="text/javascript" src="' . FormaLms\lib\Get::rel_path('lms') . '/modules/scorm/ScormApi.js' . "?v=$rnd" . '"></SCRIPT>' . "\n";
    echo '<SCRIPT type="text/javascript" src="' . FormaLms\lib\Get::rel_path('lms') . '/modules/scorm/player.js' . "?v=$rnd" . '"></SCRIPT>' . "\n";
    echo '<SCRIPT type="text/javascript" src="' . FormaLms\lib\Get::rel_path('lms') . '/modules/scorm/StdPlayer.js' . "?v=$rnd" . '"></SCRIPT>' . "\n";
    echo '<SCRIPT type="text/javascript" >' . "\n";
    echo '<!--' . "\n";

    echo "var playerConfig = {\n";
    echo " autoplay: '$autoplay',\n";
    echo " backurl: '$back_url',\n";
    echo " xmlTreeUrl: '$xmlTreeUrl',\n";
    echo " host: '{$_SERVER['HTTP_HOST']}',\n";
    echo " lms_url: '$lms_url',\n";
    echo " lms_base_url: '$lms_base_url',\n";
    echo " scormserviceid: '$scormserviceid',\n";
    echo " scormVersion: '$scormVersion',\n";
    echo " idUser: '$idUser',\n";
    echo " idReference: '$idReference',\n";
    echo " idscorm_organization: '$idscorm_organization',\n";
    echo " imagesPath: '$imagesPath',\n";
    echo " idElemTree: 'treecontent',\n";
    echo " idElemSco: 'scormbody',\n";
    echo " idElemScoContent: 'scocontent',\n";
    echo " idElemSeparator: 'separator',\n ";
    echo " showTree: '$isshow_tree',\n ";
    echo " playertemplate: '$playertemplate',\n";
    echo " keepalivetmo: '" . $keepalivetmo . "',\n";
    echo " auth_request: '" . Util::getSignature() . "',\n";

    echo " environment: '$environment',\n";
    echo " useWaitDialog: '" . FormaLms\lib\Get::sett('use_wait_dialog', 'off') . "',\n";

    echo ' startFromChapter: ' . ($start_from_chapter ? "'" . $start_from_chapter . "'" : 'false') . "\n";

    echo "};\n";

    echo 'window.onload = StdUIPlayer.initialize;' . "\n";
    echo ' // -->' . "\n";
    echo '</SCRIPT>' . "\n";

    echo '
		<script type="text/javascript" src=".././addons/yui/utilities/utilities.js"></script>
		<script type="text/javascript" src=".././addons/yui/json/json-min.js"></script>
		<script type="text/javascript" src=".././addons/yui/animation/animation-min.js"></script>
		<script type="text/javascript" src=".././addons/yui/logger/logger-min.js"></script>

		<link rel="stylesheet" type="text/css" href="' . $GLOBALS['where_templates_relative'] . '/standard/yui-skin/logger.css" />';

    echo '</head>' . "\n";

    echo '<body class="yui-skin-sam" id="page_head" class="' . $playertemplate . '" onunload="trackUnloadOnLms()">
	<div id="treecontent" class="treecontent' . $class_extension . ' ' . $playertemplate . '_menu" style="z-index: 4000;">
		<div class="menubox">Menu</div>
		<br />
	</div>
	<div id="separator" class="separator' . $class_extension . '" >
		<a id="sep_command" href="#" onclick="showhidetree();">
			<img src="' . $imagesPath . '../scorm/' . ($nItem > 1 ? 'bt_sx' : 'bt_dx') . '.png" alt="Expand/Collapse" />
		</a>
	</div>
	<div id="scocontent" class="scocontent' . $class_extension . '">
		<iframe id="scormbody" name="scormbody" frameborder="0" marginwidth="0" marginheight="0" framespacing="0" width="100%" height="100%">
		</iframe>
	</div>
	<div id="log_reader" style="position:absolute;background:#fff;"></div>';
    /*
    echo '<script type="text/javascript">
        var yl_debug = true;
        var yl_reset_timeout;
        if (!yl_debug) {
            var yl_debug =false;
        }
        // Put a LogReader on your page
        yuiLogReader = new YAHOO.widget.LogReader("log_reader", {
            verboseOutput:false,
            top:\'2px\',
            right:\'2px\',
            width:\'80%\',
            height:\'500px\',
            footerEnabled: false
        });
        yuiLogReader.collapse();
        //yuiLogReader.hide();

        function yuiLogAutoReset() {
            yuiLogReader.show();
            yuiLogReader.expand();
            //clearTimeout(yl_reset_timeout);
            //yl_reset_timeout =setTimeout(\'yuiLogReader.collapse(); yuiLogReader.hide(); yuiLogReader.clearConsole();\', 30000);
        }
        function yuiLogMsg(msg, type) {
            if (!yl_debug) { return false; }
            if (yuiLogReader.isCollapsed) {
                //yuiLogAutoReset();
            }
            if (type == \'\') {
                type = \'info\';
            }
            YAHOO.log(msg, type);
        }
    </script>';
    */

    echo '</body>
</html>';

    ob_end_flush();
    exit;	// to avoid index.php to add additional and unuseful html
}
