<?php

namespace appCore\Template\Extenstions;

use Twig\TwigFunction;

class LayoutExtension extends \Twig\Extension\AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('Layout_getPath', [\Layout::class, 'path'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getZone', [\Layout::class, 'zone'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getLangCode', [\Layout::class, 'lang_code'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getTitle', [\Layout::class, 'title'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getMeta', [\Layout::class, 'meta'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getRtl', [\Layout::class, 'rtl'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getAnalytics', [\Layout::class, 'analytics'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getResetter', [\Layout::class, 'resetter'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getAnalytics', [\Layout::class, 'analytics'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getAnalytics', [\Layout::class, 'analytics'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getCopyright', [\Layout::class, 'copyright'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getCatalogue', [\Layout::class, 'get_catalogue'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getAccessibility', [\Layout::class, 'accessibility'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_buildLanguage', [\Layout::class, 'buildLanguages'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getCart', [\Layout::class, 'cart'], ['is_safe' => ['html']]),
            new TwigFunction('Layout_getChangeLang', [\Layout::class, 'change_lang'], ['is_safe' => ['html']])
        ];
    }
}