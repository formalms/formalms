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

namespace FormaLms\lib\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder as SymfonyJsonEncoder;

/**
 * Safe JSON encoder/decoder that never throws.
 * - On decode: returns $context['default_on_error'] if provided, otherwise false.
 * - On encode: returns $context['default_on_error'] if provided; otherwise 'null'.
 */
class SafeJsonEncoder extends SymfonyJsonEncoder
{
    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        // Treat null and empty strings as invalid JSON input
        if ($data === null || (is_string($data) && trim($data) === '')) {
            return $context['default_on_error'] ?? false;
        }

        try {
            return parent::decode($data, $format, $context);
        } catch (\Throwable $e) {
            return $context['default_on_error'] ?? false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = [])
    {
        try {
            return parent::encode($data, $format, $context);
        } catch (\Throwable $e) {
            if (array_key_exists('default_on_error', $context)) {
                return $context['default_on_error'];
            }
            // Reasonable default for JSON output
            return 'null';
        }
    }
}
