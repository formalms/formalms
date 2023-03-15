<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

include 'bootstrap.php';

header('Content-type: text/plain');
header('Content-Disposition: attachment; filename="config.php"');

$fn = _installer_ . '/data/config_template.php';
echo generateConfig($fn);
