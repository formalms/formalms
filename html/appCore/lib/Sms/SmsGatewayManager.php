<?php

class SmsGatewayManager
{
    /**
     * @param array $recipients
     * @param $text
     * @param null $type
     * @return mixed
     * @throws SmsGatewayException
     */
    public static function send($recipients = array(), $text, $type = null)
    {
        $smsGateway = self::getGateway();
        $result = $smsGateway->send($recipients, strip_tags($text));
    }

    /**
     * @return array
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
        switch (Get::sett('sms_gateway')) {
            case "skebby":
            default:
                require_once(Docebo::inc(_adm_ . '/lib/Sms/SkebbySmsGateway.php'));
                $smsGateway = new SkebbySmsGateway();
                break;
        }
        return $smsGateway;
    }
}