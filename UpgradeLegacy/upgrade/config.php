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

//  4xxx : docebo ce versions series 4.x.x
// 1xxxx : forma     versions series 1.x  (formely 1.xx.xx )
// 2xxxx : forma     versions series 2.x  (formely 2.xx.xx )
require_once _legacy_adm_ . '/versions.php';

function versionSort($a, $b)
{
    return 1 * version_compare($a, $b);
}

$readFolder = _upgrader_ . '/version/';
$subFolders = [];
$versions = [];
$arrGlobal = [];
if ($handle = opendir($readFolder)) {
    while ($file = readdir($handle)) {
        if (is_dir("{$readFolder}/{$file}")) {
            if ($file != '.' & $file != '..') {
                $subFolders[] = $file;
            }
        } else {
            if ($file != '.' & $file != '..' & substr($file, -5) == '.json') {
                $versions[] = substr($file, 0, -5);
            }
        }
    }
}
closedir($handle);

usort($versions, 'versionSort');

// for reference old docebo ce versions
$GLOBALS['cfg']['versions'] = [
    '3603' => '3.6.0.3  - Docebo CE',
    '3604' => '3.6.0.4 - Docebo CE',
    '3605' => '3.6.0.5 - Docebo CE',
    '4000' => '4.0.0 - Docebo CE',
    '4010' => '4.0.1 - Docebo CE',
    '4020' => '4.0.2 - Docebo CE',
    '4030' => '4.0.3 - Docebo CE',
    '4040' => '4.0.4 - Docebo CE',
    '4050' => '4.0.5 - Docebo CE',
];

foreach ($versions as $version) {
    $strJsonVer = file_get_contents("{$readFolder}/{$version}.json", 'r');
    $arrJsonVer = json_decode($strJsonVer, true);
    $arrGlobal[$arrJsonVer['version']['number']] = $arrJsonVer['version']['name'];
    $GLOBALS['cfg']['versions'][$arrJsonVer['version']['number']] = $arrJsonVer['version']['name'];
    $GLOBALS['cfg']['detailversions'][$arrJsonVer['version']['number']] = $arrJsonVer['version'];
    if (strcmp($arrJsonVer['version']['name'], _file_version_) === 0) {
        $GLOBALS['cfg']['endversion'] = $arrJsonVer['version']['number'];
    }
}

// for reference old docebo ce versions
$GLOBALS['cfg']['docebo_versions'] = [
    '3603' => '3.6.0.3',
    '3604' => '3.6.0.4',
    '3605' => '3.6.0.5',
    '4000' => '4.0.0',
    '4010' => '4.0.1',
    '4020' => '4.0.2',
    '4030' => '4.0.3',
    '4040' => '4.0.4',
    '4050' => '4.0.5',
];
