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

interface SmsGatewayInterface
{
    /**
     * @param array $recipients
     * @param $text
     * @param null $type
     *
     * @return mixed
     *
     * @throws SmsGatewayException
     */
    public function send($recipients = [], $text, $type = null);

    /**
     * @return array
     *
     * @throws SmsGatewayException
     */
    public function getCredit();
}
