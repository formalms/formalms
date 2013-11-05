<?php

	define("IN_FORMA", "ok");
	include('../config.php');
	error_reporting(0);
	$db = mysql_connect($cfg['db_host'], $cfg['db_user'], $cfg['db_pass']);
	mysql_select_db($cfg['db_name']);

	$today = getdate(); 
	$mday = $today['mday']; 
	if ($mday < 10) $mday = "0".$mday;
	$month = $today['mon']; 
	if ($month < 10) $month = "0".$month;
	$year = $today['year'];	 
	$ore = $today['hours'];
	if ($ore < 10) $ore = "0".$ore;
	$min = $today['minutes'];
	if ($min < 10) $min = "0".$min;
	$sec = $today['seconds'];
	if ($sec < 10) $sec= "0".$sec;	
	$file_parameters = $mday."-".$month."-".$year."_h".$ore."_".$min."_".$sec; 
  
  	$query = "SELECT code, name FROM learning_course_date WHERE id_course=".$_POST['id_course']." AND id_date=".$_POST['id_date'];
	$res = mysql_query($query, $db);
	$row = mysql_fetch_array($res);
	$course_code = $row[0];	
	$edition_name = $row[1];
  
	header("Content-type: application/x-msdownload");
	header("Content-Disposition: attachment; filename=export_presenze_[".$course_code."]_".$file_parameters.".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	$array_date = array();
	
	print $edition_name;
	print "<table border=1><tr><td><b>Username</b></td><td><b>Nome Completo</b><td>Descriz zona agenzia</td></td>";
	$query = "SELECT DISTINCT day FROM learning_course_date_presence WHERE day<>'0000-00-00' AND id_date=".$_POST['id_date']." ORDER BY day";
	$res = mysql_query($query, $db);
	while ($row = mysql_fetch_array($res)) {
		print "<td><b>".substr($row[0],8,2)."-".substr($row[0],5,2)."-".substr($row[0],0,4)."</b></td>";
		array_push($array_date, $row[0]);
	}
	print "<td><b>Blocco note</b></td></tr>";

	$query = "SELECT U.userid, U.firstname, U.lastname, U.idst FROM learning_course_date_user L, core_user U WHERE L.id_user=U.idst AND L.id_date=".$_POST['id_date']." ORDER BY id_user";
	$res = mysql_query($query, $db);
	while ($row = mysql_fetch_array($res)) {
		print "<tr><td>".substr($row[0],1,strlen($row[0]))."</td><td>".$row[2]." ".$row[1]."</td>";

		$query = "SELECT user_entry FROM core_field_userentry WHERE id_common=43 AND id_user=".$row[3];
		$res2 = mysql_query($query, $db);
		$row2 = mysql_fetch_array($res2);
		$descriz_zona_ag = $row2[0];
		if (is_null($descriz_zona_ag)) $descriz_zona_ag = "";
		print "<td>".$descriz_zona_ag."</td>";
		
		for ($i=0; $i<count($array_date); $i++) {
			$query = "SELECT presence FROM learning_course_date_presence WHERE id_date=".$_POST['id_date']." AND id_user=".$row[3]." AND day='".$array_date[$i]."'";
			$res2 = mysql_query($query, $db);
			$row2 = mysql_fetch_array($res2);
			if ($row2[0]==0) print "<td>&nbsp;</td>";
			else print "<td>X</td>";
		}
		$query = "SELECT note FROM learning_course_date_presence WHERE id_date=".$_POST['id_date']." AND id_user=".$row[3]." AND day='0000-00-00'"; 
		$res3 = mysql_query($query, $db);
		$row3 = mysql_fetch_array($res3);
		print "<td>".$row3[0]."</td></tr>";
	}
	
	print "</table>";

?>