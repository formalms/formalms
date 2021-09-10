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
require(dirname(__FILE__) . '/../base.php');

// start buffer
ob_start();

// initialize
require(_base_ . '/lib/lib.bootstrap.php');
Boot::init(BOOT_DATETIME);

if (!function_exists("report_log")) {
    function report_log($string)
    {
        ob_end_flush();
        $curtime = date("d-m-Y G:i:s");
        echo "[$curtime] $string" . PHP_EOL . "\r\n" . "\n";
        ob_start();
    }
}

require_once(_adm_ . '/lib/lib.permission.php');
require_once(_base_ . '/lib/lib.pagewriter.php');

//--- here the specific code ---------------------------------------------------
//#17598 - REPORT - CRON REPORT RICHIEDE UTENTE LOGGATO (patch)
$roleid = '/admin/view';
$GLOBALS['user_roles'][$roleid] = true;
$roleid = '/admin/view_all';
$GLOBALS['user_roles'][$roleid] = true;

setLanguage('english');

function getEmailForSchedule($schedule): array
{
    $recipients = [];
    $emails = [];
    $querySchedule = "SELECT id_user FROM %lms_report_schedule_recipient WHERE id_report_schedule=" . $schedule['id_report_schedule'];
    $scheduleResult = sql_query($querySchedule);

    foreach ($scheduleResult as $recipientItem) {
        $recipients[] = $recipientItem['id_user']; //idst of the recipients
    }

    $recipients = Docebo::aclm()->getAllUsersFromSelection($recipients);
    if (!empty($recipients)) {
        $queryEmails = "SELECT email FROM %adm_user WHERE idst IN (" . implode(',', $recipients) . ") AND email<>'' AND valid = 1";
        $emailsResult = sql_query($queryEmails);
        foreach ($emailsResult as $emailItem) {
            $emails[] = $emailItem['email'];
        }
    }
    return $emails;
}


function getReportRecipients($id_rep)
{
    $output = [];
    $selected_schedules = [];
    $current_time = date('H:i');

    //check for daily

    $qry = "
			SELECT * FROM %lms_report_schedule
			WHERE period LIKE '%day%'
			AND id_report_filter=$id_rep
			AND time < '$current_time'
			AND enabled = 1
			AND (last_execution is null OR last_execution < CURDATE())
		";
    $res = sql_query($qry);

    foreach ($res as $schedule) {

        $emails = getEmailForSchedule($schedule);
        if (count($emails) > 0) {
            array_push($output, ...$emails);
            $selected_schedules[] = [
                'id_report_schedule' => $schedule['id_report_schedule'],
                'period' => 'day'
            ];
        }
    }


    //cerca i report da eseguire prima possibile

    $qry = "
				SELECT * FROM %lms_report_schedule
				WHERE period LIKE '%now%'
				AND id_report_filter=$id_rep
				AND enabled = 1
			";
    $res = sql_query($qry);

    foreach ($res as $schedule) {

        $emails = getEmailForSchedule($schedule);
        if (count($emails) > 0) {
            array_push($output, ...$emails);
            $selected_schedules[] = [
                'id_report_schedule' => $schedule['id_report_schedule'],
                'period' => 'day'
            ];
        }
    }


    //check for weekly
    $daynumber = date('w');

    $qry = "
				SELECT * FROM %lms_report_schedule
				WHERE period LIKE '%week,$daynumber%'
				AND id_report_filter=$id_rep
				AND time < '$current_time'
				AND enabled = 1
				AND (last_execution is null OR last_execution < CURDATE())
			";
    $res = sql_query($qry);

    foreach ($res as $schedule) {

        $emails = getEmailForSchedule($schedule);
        if (count($emails) > 0) {
            array_push($output, ...$emails);
            $selected_schedules[] = [
                'id_report_schedule' => $schedule['id_report_schedule'],
                'period' => 'day'
            ];
        }
    }

    //check for monthly
    $monthdaynumber = date('j'); //today's day of the month, 1-31
    $monthdays = date('t'); //amount of days in current month 28-31


    $options = [];
    if ($monthdays < 31 && $monthdaynumber == $monthdays) { //if it's the last day of tehe month
        for ($i = 31; $i >= $monthdays; $i--) {
            $options[] = "'month,$i'";
        }
    } else {
        $options[] = "'month,$monthdaynumber'";
    }

    $qry = "
			SELECT * FROM %lms_report_schedule
			WHERE period IN (" . implode(',', $options) . ")
			AND id_report_filter=$id_rep
			AND time < '$current_time'
			AND enabled = 1
			AND (last_execution is null OR last_execution < CURDATE())
		";
    $res = sql_query($qry);

    foreach ($res as $schedule) {

        $emails = getEmailForSchedule($schedule);
        if (count($emails) > 0) {
            array_push($output, ...$emails);
            $selected_schedules[] = [
                'id_report_schedule' => $schedule['id_report_schedule'],
                'period' => 'day'
            ];
        }
    }

    return [
        'recipients' => array_unique($output),
        'schedules' => $selected_schedules
    ];

}

function adaptFileName($fname)
{
    return preg_replace("/[^A-Za-z0-9 ]/", "_", $fname) . '_' . date('Y-m-d_H-i-s');
}

/**
 * Recursively deletes a directory and all its content
 * @param string $dir Directory path to be deleted
 * @return bool returns true if no errors
 */
function recursive_delete_directory($dir)
{
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!recursive_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

/**
 * Updates executed schedules: deletes one shot schedules and updates the value of last_execution for others
 * @param array $schedules array of schedule ids to be updated
 */
function update_schedules($schedules)
{

    foreach ($schedules as $schedule) {
        $id_report_schedule = $schedule['id_report_schedule'];
        $period = $schedule['period'];
        switch ($period) {
            case 'now':
                $qry = "DELETE FROM %lms_report_schedule WHERE id_report_schedule = $id_report_schedule";
                sql_query($qry);
                $qry = "DELETE FROM %lms_report_schedule_recipient WHERE id_report_schedule = $id_report_schedule";
                sql_query($qry);
                break;
            default:
                $qry = "UPDATE %lms_report_schedule SET last_execution = now() WHERE id_report_schedule = $id_report_schedule";
                sql_query($qry);
                break;
        }
    }
}

function parseBaseUrlFromRequest($atRoot = FALSE, $atCore = FALSE, $parse = FALSE)
{
    if (isset($_SERVER['HTTP_HOST'])) {
        $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
        $hostname = $_SERVER['HTTP_HOST'];
        $dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

        $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), NULL, PREG_SPLIT_NO_EMPTY);
        $core = $core[0];

        $tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
        $end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
        $base_url = sprintf($tmplt, $http, $hostname, $end);
    } else $base_url = 'http://localhost/';

    if ($parse) {
        $base_url = parse_url($base_url);
        if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
    }

    return $base_url;
}

//******************************************************************************


$report_persistence_days = Get::sett('report_persistence_days', 30);
$report_max_email_size = Get::sett('report_max_email_size_MB', 0);
$report_store_folder = Get::sett('report_storage_folder', '/' . _folder_files_ . '/common/report/');
$base_url = Get::sett('url', '');
if (empty($base_url)) {
    $base_url = parseBaseUrlFromRequest(true);
}
$report_uuid_prefix = 'uuid';


require_once(_base_ . '/lib/lib.upload.php');


$mailer = DoceboMailer::getInstance();

require_once(_base_ . '/lib/lib.json.php');
$json = new Services_JSON();


$path = _files_ . '/tmp/';
$qry = "SELECT * FROM %lms_report_filter";
$res = sql_query($qry);
sl_open_fileoperations();


$log_opened = false;

//apply an execution lock by occupying port 9999
/** @var resource|bool $lock_stream */
$lock_stream = !Get::cfg('CRON_SOCKET_SEMAPHORES', false) || @stream_socket_server('tcp://0.0.0.0:9999', $errno, $errmsg);

if ($lock_stream) {

    foreach ($res as $row) {

        $recipients_data = getReportRecipients($row['id_filter']);
        $recipients = $recipients_data['recipients'];

        if (count($recipients) > 0) {

            if (!$log_opened) {
                report_log("STARTING REPORT EXECUTION ...");
                $log_opened = true;
            }

            $schedules = $recipients_data['schedules'];

            $data = unserialize($row['filter_data']);

            $query_report = "SELECT class_name, file_name, report_name "
                . " FROM %lms_report "
                . " WHERE id_report = '" . $data['id_report'] . "'";
            $re_report = sql_query($query_report);
            if ($re_report && sql_num_rows($re_report)) {

                list($class_name, $file_name, $report_name) = sql_fetch_row($re_report);

                if ($file_name) {
                    if (file_exists(_base_ . '/customscripts/' . _folder_lms_ . '/admin/modules/report/' . $file_name) && Get::cfg('enable_customscripts', false) == true) {
                        require_once(_base_ . '/customscripts/' . _folder_lms_ . '/admin/modules/report/' . $file_name);
                    } else {
                        require_once(Forma::inc(_lms_ . '/admin/modules/report/' . $file_name));
                    }
                    $temp = new $class_name($data['id_report']);
                } else {
                    $pg = new PluginManager('Report');
                    $temp = $pg->get_plugin(strtolower($class_name), array($data['id_report']));
                }


                $temp->author = $row['author'];

                $tmpfile = adaptFileName($row['filter_name']) . '.xls';

                $start_time = microtime(true);
                $file = fopen($path . $tmpfile, "w");
                fwrite($file, $temp->getXLS($data['columns_filter_category'], $data));
                fclose($file);
                $execution_time_secs = round(microtime(true) - $start_time, 0);
                $execution_time_secs = ltrim(sprintf('%02dh%02dm%02ds', floor($execution_time_secs / 3600), floor(($execution_time_secs / 60) % 60), ($execution_time_secs % 60)), '0hm');
                if ($execution_time_secs == 's') $execution_time_secs = '0s';
                report_log($row['filter_name'] . ': Report generated in ' . $execution_time_secs);

                //Gets XLS size in MB
                clearstatcache($path . $tmpfile);
                $report_size = filesize($path . $tmpfile);

                $attachment = $path . $tmpfile;
                $attachmentName = $row['filter_name'] . '.xls';

                //Checks if report should be sent by link or attachment
                if ($report_size > $report_max_email_size * 1048576) {

                    $abs_report_folder = _base_ . $report_store_folder;
                    $report_url = trim($base_url, '/') . "/$report_store_folder";

                    //Create report storage folder if not exists
                    if (!file_exists($abs_report_folder) || !is_dir($abs_report_folder)) {
                        if (!mkdir($abs_report_folder, '0777', true) && !is_dir($abs_report_folder)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $abs_report_folder));
                        }
                    }

                    //Cleans report storage folder from expired reports
                    $now = time();
                    $uuid_folders = glob($abs_report_folder . "$report_uuid_prefix*");
                    foreach ($uuid_folders as $uuid_folder) {
                        if (is_dir($uuid_folder)) {
                            $uuid_folder_time = filemtime($uuid_folder);
                            $creation_time = $now - $uuid_folder_time;
                            if ($creation_time > $report_persistence_days * 24 * 60 * 60) {
                                $rm_result = recursive_delete_directory($uuid_folder);
                            }
                        }

                    }

                    //Computes an unique progressive ID and a token
                    $uuid = uniqid($report_uuid_prefix . time(), true);
                    $token = uniqid('', true);

                    //Computes report filename
                    $abs_report_folder .= "$uuid/$token/";
                    $report_url .= "$uuid/$token/";
                    if (!mkdir($abs_report_folder, 0777, true) && !is_dir($abs_report_folder)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $abs_report_folder));
                    }

                    $async_report = $abs_report_folder . $tmpfile;
                    $report_url .= rawurlencode($tmpfile);

                    copy($path . $tmpfile, $async_report);

                    //Sends an email containing the report link
                    $subject = 'Sending scheduled report : ' . $row['filter_name'];
                    $body = "You can download this report from <a href='$report_url'>here</a><br><br>
								WARNING: This report will be available for $report_persistence_days days, after that it will be deleted from our system and it will not be accessible anymore.";

                    $attachment = false;
                    $attachmentName = false;
                } else {
                    $subject = 'Sending scheduled report : ' . $row['filter_name'];
                    $body = date('Y-m-d H:i:s');
                }

                $mailer->Subject = 'Sending scheduled report : ' . $row['filter_name'];

                $error = false;

                if (!$mailer->SendMail(Get::sett('sender_event'), //sender
                    $recipients, //recipients
                    $subject, //subject
                    $body, //body
                    $attachment,
                    $attachmentName
                //params
                )) {
                    report_log($row['filter_name'] . ': Error while sending mail.' . $mailer->ErrorInfo);
                    $error = true;
                } else {
                    report_log($row['filter_name'] . ': Mail sent to ' . implode(' - ',$recipients));
                }


                if (!$error) {
                    update_schedules($schedules);
                    report_log($row['filter_name'] . ': Schedule info updated');
                }

                //delete temp file
                unlink($path . $tmpfile . '');
            }
        }
    }

} else {
    report_log('There is an active lock, execution aborted');
}
sl_close_fileoperations();
//output log data


//------------------------------------------------------------------------------

// finalize
Boot::finalize();

//Removes lock file, if set
if ($lock_stream)
    fclose($lock_stream);

