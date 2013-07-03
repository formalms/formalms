<?php

require_once(_base_.'/lib/lib.json.php');

$startIndex = Get::req('startIndex', DOTY_INT, 0);
$results = Get::req('results', DOTY_INT, 15);
$sort = Get::req('sort', DOTY_STRING, 'id');
$dir = Get::req('dir', DOTY_STRING, 'asc');

$query="SELECT idst as id, userid as name, firstname, lastname, email FROM core_user ORDER BY ".$sort." ".$dir." LIMIT ".$startIndex.", ".$results;
$res = sql_query($query);
$temp = array();
while ($row = mysql_fetch_assoc($res)) {
	$temp[] = array(
		'id' => $row['id'],
		'name' => $row['name'],
		'firstname' => $row['firstname'],
		'lastname' => $row['lastname'],
		'email' => $row['email']
	);

}


list($totalRecords) = sql_fetch_row(sql_query("select count(*) from core_user"));

$output = array(
	'startIndex' => (int)$startIndex,
	'recordsReturned' => count($temp),//'results' => count($temp),
	'pageSize' => $results,
	'totalRecords' => (int)$totalRecords,
	'sort' => $sort,
	'dir' => $dir,
	'records' => $temp
);


$json = new Services_JSON();
aout($json->encode($output));


?>
