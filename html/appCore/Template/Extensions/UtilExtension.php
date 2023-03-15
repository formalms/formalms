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
            }),
        ];
    }
}
