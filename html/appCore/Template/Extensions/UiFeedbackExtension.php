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

require_once _base_ . '/lib/lib.form.php';

use Twig\TwigFunction;

class UiFeedbackExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('UiFeedback_info', [\UiFeedback::class, 'info'], ['is_safe' => ['html']]),
            new TwigFunction('UiFeedback_notice', [\UiFeedback::class, 'notice'], ['is_safe' => ['html']]),
            new TwigFunction('UiFeedback_error', [\UiFeedback::class, 'error'], ['is_safe' => ['html']]),
            new TwigFunction('UiFeedback_pinfo', [\UiFeedback::class, 'pinfo'], ['is_safe' => ['html']]),
            new TwigFunction('UiFeedback_pnotice', [\UiFeedback::class, 'pnotice'], ['is_safe' => ['html']]),
            new TwigFunction('UiFeedback_perror', [\UiFeedback::class, 'perror'], ['is_safe' => ['html']]),
        ];
    }
}
