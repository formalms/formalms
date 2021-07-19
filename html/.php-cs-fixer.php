<?php

$year = date('Y');

$headerComment = <<<EOF
This file is part of the Forma package.

(c) {$year} Giuseppe Nucifora <giuseppe.nucifora@purplenetwork.it>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/api')
    ->in(__DIR__ . '/appCore')
    ->in(__DIR__ . '/appLms')
    ->in(__DIR__ . '/appScs')
    ->in(__DIR__ . '/cron')
    ->in(__DIR__ . '/eventListeners')
    ->in(__DIR__ . '/Exceptions')
    ->in(__DIR__ . '/install')
    ->in(__DIR__ . '/lib')
    ->in(__DIR__ . '/upgrade')
    ->in(__DIR__ . '/widget');

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
