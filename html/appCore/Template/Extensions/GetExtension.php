<?php


namespace appCore\Template\Extensions;

require_once _base_.'/lib/lib.get.php';

use Twig\TwigFunction;

class GetExtension extends \Twig\Extension\AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('Get_title', [\Get::class, 'title'],['is_safe' =>['html']]),
            new TwigFunction('Get_sprite', [\Get::class, 'sprite'],['is_safe' =>['html']]),
            new TwigFunction('Get_relPath', [\Get::class, 'rel_path'],['is_safe' =>['html']]),
            new TwigFunction('Get_getSetting', [\Get::class, 'sett'],['is_safe' =>['html']]),
            new TwigFunction('Get_getTemplatePath', [\Get::class, 'tmpl_path'],['is_safe' =>['html']]),
        ];
    }
}