<?php
include('bootstrap.php');

header('Content-type: text/plain');
header('Content-Disposition: attachment; filename="config.php"');

$fn = _installer_."/data/config_template.php";
echo generateConfig($fn);