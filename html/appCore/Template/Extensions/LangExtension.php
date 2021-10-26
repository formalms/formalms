<?php

namespace appCore\Template\Extensions;

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
    private function getOldTranslate(){
        return new TwigFunction('translate', [\Lang::class, 't'], ['is_safe' => ['html']]);
    }
}