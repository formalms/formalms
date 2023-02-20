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

class SmsGatewayManager
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
    public static function send($recipients = [], $text, $type = null)
    {
        $smsGateway = self::getGateway();
        try {
            $response = $smsGateway->send($recipients, strip_tags($text), $type);
        } catch (SmsGatewayException $e) {
            return false;
        }
    }

    /**
     * @return array
     *
     * @throws SmsGatewayException
     */
    public static function getCredit()
    {
        $smsGateway = self::getGateway();

        return $smsGateway->getCredit();
    }

    /**
     * @return SkebbySmsGateway
     */
    public static function getGateway()
    {
        switch (FormaLms\lib\Get::sett('sms_gateway')) {
            case 'skebby':
            default:
                require_once Forma::inc(_adm_ . '/lib/Sms/SkebbySmsGateway.php');
                $smsGateway = new SkebbySmsGateway();
                break;
        }

        return $smsGateway;
    }
}
