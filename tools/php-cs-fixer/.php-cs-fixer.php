<?php

$year = date('Y');

$headerComment = <<<EOF
FORMA - The E-Learning Suite

Copyright (c) 2013-{$year} (Forma)
https://www.formalms.org
License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt

from docebo 4.0.5 CE 2008-2012 (c) docebo
License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
EOF;


$finder = PhpCsFixer\Finder::create()
    ->exclude(__DIR__ . '/../../html/vendor')
    ->exclude(__DIR__ . '/../../html/plugins')
    ->exclude(__DIR__ . '/../../html/files')
    ->in(__DIR__ . '/../../html/plugins/ConferenceBBB')
    ->in(__DIR__ . '/../../html/plugins/Dummy')
    ->in(__DIR__ . '/../../html/plugins/FacebookAuth')
    ->in(__DIR__ . '/../../html/plugins/FormaAuth')
    ->in(__DIR__ . '/../../html/plugins/GoogleAuth')
    ->in(__DIR__ . '/../../html/plugins/LinkedinAuth')
    ->in(__DIR__ . '/../../html/plugins/report_aggregate')
    ->in(__DIR__ . '/../../html/plugins/report_course')
    ->in(__DIR__ . '/../../html/plugins/report_user')
    ->in(__DIR__ . '/../../html/plugins/TwitterAuth')
    ->in(__DIR__ . '/../../html/');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => true,
        'concat_space' => ['spacing' => 'one'],
        'array_syntax' => ['syntax' => 'short'],
        'header_comment' => ['header' => $headerComment],
        'yoda_style' => false,
    ])
    ->setFinder($finder);
