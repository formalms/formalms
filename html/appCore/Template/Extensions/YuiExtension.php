<?php


namespace appCore\Template\Extensions;

require_once(_base_.'/lib/lib.form.php');

use Twig\TwigFunction;

class YuiExtension extends \Twig\Extension\AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('Yui_load', [\YuiLib::class, 'load'],['is_safe' =>['html']]),
        ];
    }

}