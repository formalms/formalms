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

require_once(_base_.'/lib/lib.download.php' );

function env_play($lobj, $options) {

	list($file) = sql_fetch_row(sql_query("SELECT path"
		. " FROM %lms_materials_lesson"
		. " WHERE idLesson = ".(int)$lobj->id.""));

	if(!$file) Util::jump_to($lobj->back_url);

	$id_param = $lobj->getIdParam();

	if($lobj->id_reference != false) {
		require_once(_lms_.'/class.module/track.item.php' );
		$ti = new Track_Item($lobj, Docebo::user()->getIdSt()); // need id_resource, id_reference, type and environment
		$ti->setDate(date('Y-m-d H:i:s'));
		$ti->status = 'completed';
		$ti->update();
	}
	Util::download('/appLms/'.Get::sett('pathlesson'), $file);
}

function play( $idResource, $idParams, $back_url ) {
	//if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
//echo ("idResource = ".$idResource."; idParams = ".$idParams."; back_url = ".$back_url);
	list($file) = sql_fetch_row(sql_query("SELECT path"
											. " FROM ".$GLOBALS['prefix_lms']."_materials_lesson"
											. " WHERE idLesson = '".$idResource."'"));
											
	//recognize mime type
	$expFileName = explode('.', $file);
	$totPart = count($expFileName) - 1;

	require_once( $GLOBALS['where_lms'].'/lib/lib.param.php' );
	$idReference = getLOParam($idParams, 'idReference');
	// NOTE: Track only if $idReference is present 
	if( $idReference !== FALSE ) {
		require_once( $GLOBALS['where_lms'].'/class.module/track.item.php' );
		list( $exist, $idTrack) = Track_Item::getIdTrack($idReference, getLogUserId(), $idResource, TRUE );
		if( $exist ) {
			$ti = new Track_Item( $idTrack );
			$ti->setDate(date('Y-m-d H:i:s'));
			$ti->status = 'completed';
			$ti->update();
		} else {
			$ti = new Track_Item( false );
			$ti->createTrack( $idReference, $idTrack, getLogUserId(), date('Y-m-d H:i:s'), 'completed', 'item' );
		}
	}

	if($_SESSION['direct_play'] == 1) {

		if (isset($_SESSION['idCourse'])) {

			TrackUser::closeSessionCourseTrack();

			unset($_SESSION['idCourse']);
			unset($_SESSION['idEdition']);
		}
		if(isset($_SESSION['test_assessment'])) unset($_SESSION['test_assessment']);
		if(isset($_SESSION['cp_assessment_effect'])) unset($_SESSION['cp_assessment_effect']);
		$_SESSION['current_main_menu'] = '1';
		$_SESSION['sel_module_id'] = '1';
		$_SESSION['is_ghost'] = false;

	}
	
	//send file
	sendFile('/appLms/'.Get::sett('pathlesson'), $file, $expFileName[$totPart]);
}
		
?>
