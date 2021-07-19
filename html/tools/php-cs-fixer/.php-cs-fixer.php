<?php

$year = date('Y');

$headerComment = <<<EOF
This file is part of the Forma package.

(c) {$year} Giuseppe Nucifora <giuseppe.nucifora@purplenetwork.it>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
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
