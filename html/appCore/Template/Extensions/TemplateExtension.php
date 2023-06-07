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

namespace FormaLms\appCore\Template\Extensions;

use Twig\TwigFunction;

require_once _lib_ . '/lib.template.php';

class TemplateExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('Template_getBackUi', 'getBackUi', ['is_safe' => ['html']]),
            new TwigFunction('Template_getVersion', fn () => getTemplateVersion(getDefaultTemplate())),
            new TwigFunction('Template_getTitleArea', function ($text, $image = '', $alt_image = '', $ignore_glob = false) {
                return getTitleArea($text, $image, $alt_image, $ignore_glob);
            }),
        ];
    }
}
