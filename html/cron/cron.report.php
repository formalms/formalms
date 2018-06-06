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

define("CORE", true);
define("IN_FORMA", true);
define("_deeppath_", '../');
require(dirname(__FILE__).'/../base.php');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_DATETIME);

// not a pagewriter but something similar
$GLOBALS['operation_result'] = '';
if(!function_exists("docebo_out")) {
	function docebo_cout($string) { $GLOBALS['operation_result'] .= $string; }
}
require_once(_adm_.'/lib/lib.permission.php');
require_once(_base_.'/lib/lib.pagewriter.php');

//--- here the specific code ---------------------------------------------------
//#17598 - REPORT - CRON REPORT RICHIEDE UTENTE LOGGATO (patch)
$roleid='/admin/view';
$GLOBALS['user_roles'][$roleid] = true;
$roleid='/admin/view_all';
$GLOBALS['user_roles'][$roleid] = true;

setLanguage('english');

function getReportRecipients($id_rep) {
	//get month, day
	$arr_days = array();
	$arr_months = array();

	$output = array();

	//check for daily

	$recipients = array();

	$qry = "SELECT * FROM %lms_report_schedule WHERE period LIKE '%day%' AND id_report_filter=$id_rep AND enabled = 1";
	$res = sql_query($qry);

	while ($row = sql_fetch_assoc($res)) {

		$qry2 = "SELECT id_user FROM %lms_report_schedule_recipient WHERE id_report_schedule=".$row['id_report_schedule'];
		$res2 = sql_query($qry2);

		while(list($recipient) = sql_fetch_row($res2)) {
			$recipients[] = $recipient; //idst of the recipients
		}

		$recipients_flat = Docebo::aclm()->getAllUsersFromSelection($recipients);
		if (!empty($recipients_flat)) {
			$qry3 = "SELECT email FROM %adm_user WHERE idst IN (".implode(',', $recipients_flat).") AND email<>'' AND valid = 1";
			$res3 = sql_query($qry3);
			while (list($email) = sql_fetch_row($res3))
				$output[] = $email;
		}
	}


	//check for weekly
	$daynumber = date('w');
	$recipients = array();

	$qry = "SELECT * FROM %lms_report_schedule WHERE period LIKE '%week,$daynumber%' AND id_report_filter=$id_rep AND enabled = 1";
	$res = sql_query($qry);

	while ($row = sql_fetch_assoc($res)) {

		$qry2 = "SELECT id_user FROM %lms_report_schedule_recipient WHERE id_report_schedule=".$row['id_report_schedule'];
		$res2 = sql_query($qry2);

		while(list($recipient) = sql_fetch_row($res2)) {
			$recipients[] = $recipient;
		}

		$recipients_flat = Docebo::aclm()->getAllUsersFromSelection($recipients);
		if (!empty($recipients_flat)) {
			$qry3 = "SELECT email FROM %adm_user WHERE idst IN (".implode(',', $recipients_flat).") AND email<>'' AND valid = 1";
			$res3 = sql_query($qry3);
			while (list($email) = sql_fetch_row($res3))
				$output[] = $email;
		}
	}

	//check for monthly
	$monthdaynumber = date('j'); //today's day of the month, 1-31
	$monthdays = date('t'); //amount of days in current month 28-31
	$recipients = array();

	$options = array();
	if ($monthdays<31 && $monthdaynumber==$monthdays) { //if it's the last day of tehe month
		for ($i=31; $i>=$monthdays; $i--) {
			$options[] = "'month,$i'";
		}
	} else {
		$options[] = "'month,$monthdaynumber'";
	}

	$qry = "SELECT * FROM %lms_report_schedule WHERE period IN (".implode(',', $options).") AND id_report_filter=$id_rep AND enabled = 1";
	$res = sql_query($qry);


	while ($row = sql_fetch_assoc($res)) {

		$qry2 = "SELECT id_user FROM %lms_report_schedule_recipient WHERE id_report_schedule=".$row['id_report_schedule'];
		$res2 = sql_query($qry2);

		while(list($recipient) = sql_fetch_row($res2)) {
			$recipients[] = $recipient;
		}

		$recipients_flat = Docebo::aclm()->getAllUsersFromSelection($recipients);
		if (!empty($recipients_flat)) {
			$qry3 = "SELECT email FROM %adm_user WHERE idst IN (".implode(',', $recipients_flat).") AND email<>'' AND valid = 1";
			$res3 = sql_query($qry3);
			while (list($email) = sql_fetch_row($res3))
				$output[] = $email;
		}
	}

	//die(print_r($output, true));
	//prepare output
	return array_unique($output);

}

function adaptFileName($fname) {
	return preg_replace("/[^A-Za-z0-9 ]/", "_", $fname).'_'.date('Y-m-d_H-i-s');
}


//******************************************************************************


require_once(_base_.'/lib/lib.upload.php');

docebo_cout('STARTING REPORT CRON ...<br /><br />');


require_once(_base_.'/lib/lib.mailer.php');
$mailer = DoceboMailer::getInstance();

require_once(_base_.'/lib/lib.json.php');
$json = new Services_JSON();


$path = _base_.'/files/tmp/';
$qry = "SELECT * FROM %lms_report_filter";
$res = sql_query($qry);
sl_open_fileoperations();
while ($row = sql_fetch_assoc($res)) {

	$recipients = getReportRecipients($row['id_filter']);

	if (count($recipients)>0) {

		$data = unserialize( $row['filter_data'] ) ;

		$query_report = "SELECT class_name, file_name, report_name "
			." FROM %lms_report "
			." WHERE id_report = '".$data['id_report']."'";
		$re_report = sql_query($query_report);
		if($re_report && sql_num_rows($re_report)) {

			list($class_name, $file_name, $report_name) = sql_fetch_row($re_report);

			require_once(_lms_.'/admin/modules/report/'.$file_name);
			$temp = new $class_name( $data['id_report'] );
			$temp->author = $row['author'];

			$tmpfile = adaptFileName($row['filter_name']).'.xls';//rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).'';

			$file = sl_fopen('/tmp/'.$tmpfile, "w");
			fwrite($file, $temp->getXLS($data['columns_filter_category'], $data));
			fclose($file);

			$mailer->Subject = 'Sending scheduled report : '.$row['filter_name'];

			$subject = 'Sending scheduled report : '.$row['filter_name'];
			$body = date('Y-m-d H:i:s');

			if (!$mailer->SendMail(
					Get::sett('sender_event'), //sender
					$recipients, //recipients
					$subject, //subject
					$body, //body
					$path.$tmpfile, $row['filter_name'].'.xls', //
					false	//params
				)) {
				docebo_cout('<b>'.$row['filter_name'].'</b> Error while sending mail.'.$mailer->ErrorInfo.'<br />' ); //: '.$mailer->getError?
			} else {
				docebo_cout('<b>'.$row['filter_name'].'</b> Mail sent to : '.implode(',', $recipients).'<br />' );
			}

			//delete temp file
			unlink($path.$tmpfile.'');
		} else {
			docebo_cout('"'.$row['id_report'].'" '.'<br />');
		}
	}


}
sl_close_fileoperations();
//output log data


//------------------------------------------------------------------------------

// finalize
Boot::finalize();

// remove all the echo
ob_clean();

// Print out the page
echo $GLOBALS['operation_result'];

// flush buffer
ob_end_flush();

?>