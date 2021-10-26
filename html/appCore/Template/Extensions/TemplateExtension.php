<?php

namespace appCore\Template\Extensions;

use Twig\TwigFunction;

require_once _lib_ . '/lib.template.php';

class TemplateExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('Template_getBackUi', 'getBackUi', ['is_safe' => ['html']])
        ];
    }
}