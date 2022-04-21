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

require_once _base_ . '/lib/lib.json.php';

$startIndex = Forma\lib\Get::req('startIndex', DOTY_INT, 0);
$results = Forma\lib\Get::req('results', DOTY_INT, 15);
$sort = Forma\lib\Get::req('sort', DOTY_STRING, 'id');
$dir = Forma\lib\Get::req('dir', DOTY_STRING, 'asc');

$query = 'SELECT idst as id, userid as name, firstname, lastname, email FROM core_user ORDER BY ' . $sort . ' ' . $dir . ' LIMIT ' . $startIndex . ', ' . $results;
$res = sql_query($query);
$temp = [];
while ($row = sql_fetch_assoc($res)) {
    $temp[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'firstname' => $row['firstname'],
        'lastname' => $row['lastname'],
        'email' => $row['email'],
    ];
}

list($totalRecords) = sql_fetch_row(sql_query('select count(*) from core_user'));

$output = [
    'startIndex' => (int) $startIndex,
    'recordsReturned' => count($temp), //'results' => count($temp),
    'pageSize' => $results,
    'totalRecords' => (int) $totalRecords,
    'sort' => $sort,
    'dir' => $dir,
    'records' => $temp,
];

$json = new Services_JSON();
aout($json->encode($output));
