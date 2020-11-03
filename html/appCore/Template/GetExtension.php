<?php


namespace appCore\Template;

require_once _base_.'/lib/lib.get.php';

use Twig\TwigFunction;

class GetExtension extends \Twig\Extension\AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('Get_title', [\Get::class, 'title'],['is_safe' =>['html']]),

        ];
    }
}