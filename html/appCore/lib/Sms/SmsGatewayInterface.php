<?php

interface SmsGatewayInterface
{
    /**
     * @param array $recipients
     * @param $text
     * @param null $type
     * @return mixed
     * @throws SmsGatewayException
     */
    public function send($recipients = array(), $text, $type = null);

    /**
     * @return array
     * @throws SmsGatewayException
     */
    public function getCredit();
}