<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace Forma\appCore\Template\Extensions;

use Twig\TwigFunction;

class GetExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('Get_title', [\Forma\lib\Get::class, 'title'], ['is_safe' => ['html']]),
            new TwigFunction('Get_sprite', [\Forma\lib\Get::class, 'sprite'], ['is_safe' => ['html']]),
            new TwigFunction('Get_absPath', [\Forma\lib\Get::class, 'abs_path'], ['is_safe' => ['html']]),
            new TwigFunction('Get_spriteLink', [\Forma\lib\Get::class, 'sprite_link'], ['is_safe' => ['html']]),
            new TwigFunction('Get_relPath', [\Forma\lib\Get::class, 'rel_path'], ['is_safe' => ['html']]),
            new TwigFunction('Get_getSetting', [\Forma\lib\Get::class, 'sett'], ['is_safe' => ['html']]),
            new TwigFunction('Get_getTemplatePath', [\Forma\lib\Get::class, 'tmpl_path'], ['is_safe' => ['html']]),
        ];
    }
}
