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
    ->exclude(__DIR__ . '/../../vendor')
    ->in(__DIR__ . '/../../');

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
