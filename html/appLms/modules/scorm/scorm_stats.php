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

/**
 *	This function print statistic about a user for a scorm organization.
 *
 *	@param int $idscorm_organization id of the organization
 *  @param int $idUser id of the user
 *
 *	@return string to output
 **/
function scorm_userstat($idscorm_organization, $idUser, $idReference = null, $mvc = false)
{
    require_once Forma::inc(_lms_ . '/modules/scorm/scorm_items_track.php');
    require_once Forma::inc(_lms_ . '/modules/scorm/CPManagerDb.php');
    require_once Forma::inc(_lms_ . '/modules/scorm/RendererBase.php');

    // get idscorm_package
    $query = 'SELECT idscorm_package, org_identifier '
            . ' FROM %lms_scorm_organizations'
            . " WHERE idscorm_organization = '" . $idscorm_organization . "'";
    $rs = sql_query($query)
            or communicationError('3');
    list($idscorm_package, $org_identifier) = sql_fetch_row($rs);

    $it = new Scorm_ItemsTrack($GLOBALS['dbConn'], $GLOBALS['prefix_lms']);
    $org_info = $it->getItemsInfo($idReference, null, $idscorm_organization);

    $output = '';
    $str = '<br />' . (!$mvc ? '<div class="std_block">' : '');
    if ($mvc) {
        $output .= $str;
    } else {
        $GLOBALS['page']->add($str, 'content');
    }

    $cpm = new CPManagerDb();
    $cpm->Open($idReference, $idscorm_package, $GLOBALS['dbConn'], $GLOBALS['prefix_lms']);
    $cpm->ParseManifest();
    $rb = new RendererDefaultImplementation();
    $rb->imgPrefix = getPathImage() . 'treeview/';
    $rb->imgOptions = 'width="24" height="24"';
    $rb->showlinks = false;
    $rb->showit = true;
    $rb->itemtrack = $it;
    $rb->idUser = $idUser;
    $rb->resBase = '';
    if (function_exists('cbMakeReportLink')) {
        $rb->linkCustomCallBack = 'cbMakeReportLink';
    }
    //$rb->renderStatusCallBack = "renderStatus";

    $str = $cpm->RenderOrganization($org_identifier, $rb);
    if ($mvc) {
        $output .= $str;
    } else {
        $GLOBALS['page']->add($str, 'content');
    }
    if (!$mvc) {
        $GLOBALS['page']->add('</div>', 'content');
    }
    if ($mvc) {
        return $output;
    }
}

// TODO: sourced from doceboLms\modules\organization\orgresults.php -- to be reviewed
function decodeSessionTime($stime)
{
    $output = $stime;
    if (strpos($stime, 'P') !== false) {
        $re1 = preg_match('/^P((\d*)Y)?((\d*)M)?((\d*)D)?(T((\d*)H)?((\d*)M)?((\d*)(\.(\d{1,2}))?S)?)?$/', $stime, $t1_s);
        if (!isset($t1_s[15]) || $t1_s[15] == '') {
            $t1_s[15] = '00';
        }
        if (!isset($t1_s[13]) || $t1_s[13] == '') {
            $t1_s[13] = '00';
        }
        if (!isset($t1_s[11]) || $t1_s[11] == '') {
            $t1_s[11] = '00';
        }
        if (!isset($t1_s[9]) || $t1_s[9] == '') {
            $t1_s[9] = '0000';
        }
        $output = ($t1_s[9] == '0000' || $t1_s[9] == '' ? '' : $t1_s[9] . ':')
            . sprintf("%'02s:%'02s.%'02s", $t1_s[11], $t1_s[13], $t1_s[15]);
    }

    return $output;
}

// TODO: sourced from appLms/modules/organization/orgresults.php -- to be reviewed
function getTrackingTable($id_user, $id_org, $idscorm_item, $idReference)
{
    require_once Forma::inc(_lib_ . '/lib.table.php');
    $tb = new Table(FormaLms\lib\Get::sett('visu_course'));

    $lang = DoceboLanguage::CreateInstance('organization', 'lms');

    $h_type = ['', '', 'image', 'image', '', 'nowrap', 'image', 'image nowrap'];
    $h_content = [
        $lang->def('_NAME'),
        $lang->def('_STATUS'),
        $lang->def('_SCORE'),
        $lang->def('_MAX_SCORE'),
        $lang->def('_DATE_LAST_ACCESS'),
        $lang->def('_TIME'),
        $lang->def('_ATTEMPTS'),
        '',
    ];

    $tb->setColsStyle($h_type);
    $tb->addHead($h_content);

    $query = 'SELECT idscorm_item, status ' .
        ' FROM %lms_scorm_items_track  ' .
        " WHERE idscorm_organization=$id_org " .
        " AND idUser=$id_user ";
    $lessons_status = [];
    $res = sql_query($query);
    while (list($id, $s) = sql_fetch_row($res)) {
        $lessons_status[$id] = $s;
    }

    $qry = 'SELECT t3.title, t1.lesson_status, t1.score_raw, t1.score_max, t1.session_time, t1.total_time, ' .
        ' MAX(t2.date_action) as last_access, COUNT(*) as attempts, t1.idscorm_item as item, t1.idscorm_tracking as id_track ' .
        ' FROM %lms_scorm_tracking as t1, ' .
        ' %lms_scorm_tracking_history as t2, ' .
        ' %lms_scorm_items as t3 ' .
        ' WHERE t1.idscorm_item=t3.idscorm_item AND ' .
        " t2.idscorm_tracking=t1.idscorm_tracking AND t3.idscorm_organization=$id_org " .
        " AND t1.idUser=$id_user AND t1.idscorm_item=$idscorm_item " .
        ' GROUP BY t2.idscorm_tracking';

    $res = sql_query($qry);
    while ($row = sql_fetch_assoc($res)) {
        $line = [];

        $interactions = '<a href="index.php?modname=stats&op=statoneuseroneiteminteractions&amp;id_user=' . $id_user . '&amp;idItem=' . $idReference . '&amp;id_track=' . $row['id_track'] . '">' . $lang->def('_SHOW_INTERACTIONS') . '</a>';
        $scorm_history = '<a href="index.php?modname=stats&op=statoneuseroneitemhistory&amp;idUser=' . $id_user . '&amp;idItem=' . $idReference . '&amp;idItemDetail=' . $row['item'] . '&amp;backto=statoneuseroneitem">' . $lang->def('_HISTORY') . '</a>';

        $line[] = $row['title'];
        $line[] = $lessons_status[$row['item']];
        $line[] = $row['score_raw'];
        $line[] = $row['score_max'];
        $line[] = Format::date($row['last_access']);
        $line[] = decodeSessionTime($row['total_time']);
        $line[] = $row['attempts'];
        $line[] = ($row['attempts'] > 1 ? $scorm_history : '');
        $tb->addBody($line);
    }
    cout($tb->getTable(), 'content');
} //end function

// TODO: sourced from appLms/modules/organization/orgresults.php -- to be reviewed
function getHistoryTable($id_user, $id_org, $idscorm_item, $idReference)
{
    require_once Forma::inc(_lib_ . '/lib.table.php');
    $tb = new Table(FormaLms\lib\Get::sett('visu_course'));

    $lang = DoceboLanguage::CreateInstance('organization', 'lms');

    $h_type = ['', '', '', '', ''];
    $h_content = [
        $lang->def('_ATTEMPT'),
        $lang->def('_STATUS'),
        $lang->def('_SCORE'),
        $lang->def('_DATE'),
        $lang->def('_TIME'),
    ];

    $tb->setColsStyle($h_type);
    $tb->addHead($h_content);

    $qry = 'SELECT t1.* FROM ' .
        $GLOBALS['prefix_lms'] . '_scorm_tracking_history as t1 JOIN ' .
        $GLOBALS['prefix_lms'] . '_scorm_tracking as t2 ON (t1.idscorm_tracking=t2.idscorm_tracking) ' .
        " WHERE t2.idscorm_item=$idscorm_item AND t2.idUser=$id_user " .
        ' ORDER BY t1.date_action ASC ';
    $res = sql_query($qry);
    $i = 1;
    while ($row = sql_fetch_assoc($res)) {
        $line = [];

        $line[] = $lang->def('_ATTEMPT') . ' ' . $i;
        $line[] = $row['lesson_status'];
        $line[] = $row['score_raw'];
        $line[] = Format::date($row['date_action']);
        $line[] = decodeSessionTime($row['session_time']);

        $tb->addBody($line);
        ++$i;
    }

    //title
    cout($tb->getTable(), 'content');
}

function scorm_userstat_detailhist($idscorm_organization, $idUser, $idItem, $idReference)
{
    return getHistoryTable($idUser, $idscorm_organization, $idItem, $idReference);
}

function scorm_userstat_detail($idscorm_organization, $idUser, $idItem, $idReference)
{
    return getTrackingTable($idUser, $idscorm_organization, $idItem, $idReference);
}

function render_node_row($node, $deep, $stack, $label = null, $isLast = false)
{
    if ($label === null) {
        $label = $node->getTagName();
    }
    $out = '<div class="report_on_tree">';
    $out .= '<span class="scorm_report_value">' . $node->getNodeValue() . '</span>';
    for ($deepIndex = 0; $deepIndex < $deep; ++$deepIndex) {
        $out .= '	<img class="TreeClass" src="' . scorm_stat_getImage($deep, $deepIndex, $isLast, $stack[$deepIndex])
                . '" width="24px"/>' . "\n";
    }

    $out .= '<span class="scorm_report_label">' . $label . '</span>';
    $out .= '</div>';

    return $out;
}

function render_half_row($deep, $stack, $label, $isLast = false)
{
    $out = '<div class="report_on_tree">';

    $out .= '<span class="scorm_report_half_value"/></span>';
    for ($deepIndex = 0; $deepIndex < $deep; ++$deepIndex) {
        $out .= '	<img class="TreeClass" src="' . scorm_stat_getImage($deep, $deepIndex, $isLast, $stack[$deepIndex])
                . '" width="24px"/>' . "\n";
    }

    $out .= '<span class="scorm_report_half_label">' . $label . '</span>';
    $out .= '</div>';

    return $out;
}

function scorm_stat_getImage($deep, $deepPos, $isLast, $isEnd)
{
    $imgLabel = '';
    require_once Forma::inc(_lms_ . '/modules/scorm/RendererBase.php');
    if ($deep == $deepPos) {
        // handle REND_TITLE
        $imgLabel = SCORMREND_TITLE;
    } elseif ($deep == $deepPos + 1) {
        // handle REND_EXPAND_INTER,REND_COLLAPSE_INTER,
        // REND_EXPAND_END,REND_COLLAPSE_END
        // REND_BRANCH_INTER,REND_BRANCH_END    // inLeaf
        if ($isLast) {
            $imgLabel = SCORMREND_BRANCH_END;
        } else {
            $imgLabel = SCORMREND_BRANCH_INTER;
        }
    } else {
        // handle REND_VERT_INTER,REND_EMPTY
        if ($isEnd) {
            $imgLabel = SCORMREND_EMPTY;
        } else {
            $imgLabel = SCORMREND_VERT_INTER;
        }
    }

    return getPathImage() . 'treeview/' . $imgLabel;
}
