<?php

namespace appCore\Template\Extenstions;

use Twig\TwigFunction;

class LangExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('Lang_translate', [\Lang::class, 't'], ['is_safe' => ['html']]),
            new TwigFunction('translate', [\Lang::class, 't'], ['is_safe' => ['html']]),

        ];
    }
}