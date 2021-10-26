<?php

namespace appCore\Template\Extensions;

use Twig\TwigFunction;

class UtilExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('Util_getSignature', function ($link = false) {
                return \Util::getSignature($link);
            }),
            new TwigFunction('Util_checkRole', function ($roleId, $returnValue = true) {
                return \checkRole($roleId, $returnValue);
            }),
            new TwigFunction('Util_getConstant', function ($value) {
                return constant($value);
            })
        ];
    }

}