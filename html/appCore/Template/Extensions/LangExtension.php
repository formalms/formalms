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

class LangExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('Lang_translate', [\Lang::class, 't'], ['is_safe' => ['html']]),
            $this->getOldTranslate(),
        ];
    }

    /** @deprecated  */
    private function getOldTranslate()
    {
        return new TwigFunction('translate', [\Lang::class, 't'], ['is_safe' => ['html']]);
    }
}
