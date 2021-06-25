<?php


namespace appCore\Template\Extenstions;

require_once(_base_.'/lib/lib.form.php');

use Twig\TwigFunction;

class UiFeedbackExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('UiFeedback_info', [\UiFeedback::class, 'info'],['is_safe' =>['html']]),
            new TwigFunction('UiFeedback_notice', [\UiFeedback::class, 'notice'],['is_safe' =>['html']]),
            new TwigFunction('UiFeedback_error', [\UiFeedback::class, 'error'],['is_safe' =>['html']]),
            new TwigFunction('UiFeedback_pinfo', [\UiFeedback::class, 'pinfo'],['is_safe' =>['html']]),
            new TwigFunction('UiFeedback_pnotice', [\UiFeedback::class, 'pnotice'],['is_safe' =>['html']]),
            new TwigFunction('UiFeedback_perror', [\UiFeedback::class, 'perror'],['is_safe' =>['html']]),

        ];
    }

}