<?php
require_once(Docebo::inc(_adm_ . '/lib/Sms/SmsGatewayInterface.php'));
require_once(Docebo::inc(_adm_ . '/lib/Sms/SmsGatewayException.php'));

class SkebbySmsGateway implements SmsGatewayInterface
{

    const NET_ERROR = "Network+error,+unable+to+send+the+message";
    const SENDER_ERROR = "You+can+specify+only+one+type+of+sender,+numeric+or+alphanumeric";

    const SMS_TYPE_CLASSIC = "classic";
    const SMS_TYPE_CLASSIC_PLUS = "classic_plus";
    const SMS_TYPE_BASIC = "basic";
    const SMS_TYPE_TEST_CLASSIC = "test_classic";
    const SMS_TYPE_TEST_CLASSIC_PLUS = "test_classic_plus";
    const SMS_TYPE_TEST_BASIC = "test_basic";

    /**
     * @return array
     * @throws SmsGatewayException
     */
    public function getCredit()
    {
        $credit_result = $this->skebbyGatewayGetCredit(
            Get::sett('sms_gateway_user'),
            Get::sett('sms_gateway_pass')
        );


        if ($credit_result['status'] == 'success') {
            return array(
                'credit_left' => $credit_result['credit_left'],
                self::SMS_TYPE_CLASSIC => $credit_result['classic_sms'],
                self::SMS_TYPE_BASIC => $credit_result['basic_sms'],
            );
        }

        if ($credit_result['status'] == 'failed') {
            $code = 0;
            if (isset($result['code'])) {
                $code = $result['code'];
            }
            throw new SmsGatewayException(urldecode($result['message']), $code);
        }
    }


    public
    function send($recipients = array(), $text, $type = SMS_TYPE_BASIC)
    {

        // ------------ SMS Classic dispatch --------------

        // SMS CLASSIC dispatch with custom alphanumeric sender
        //        $result = $this->skebbyGatewaySendSMS('gianluigi.mammarella', 'formalms', $recipients, 'Ciao Alberto! Vai su http://formalms.purplenetwork.it/', SMS_TYPE_CLASSIC, '', 'purplenetwo');

        // SMS CLASSIC dispatch with custom numeric sender
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC,'393471234567');


        // ------------- SMS Basic dispatch ----------------
        $result = $this->skebbyGatewaySendSMS(
            Get::sett('sms_gateway_user'),
            Get::sett('sms_gateway_pass'),
            $recipients,
            $text,
            $type,
            '',
            Get::sett('sms_sent_from', ''),
            '',
            'UTF-8'
        );


        // ------------ SMS Classic Plus dispatch -----------

        // SMS CLASSIC PLUS dispatch (with delivery report) with custom alphanumeric sender
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC_PLUS,'','John');

        // SMS CLASSIC PLUS dispatch (with delivery report) with custom numeric sender
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC_PLUS,'393471234567');

        // SMS CLASSIC PLUS dispatch (with delivery report) with custom alphanumeric sender and custom reference string
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC_PLUS,'393471234567','','reference');


        //  ------------------------------------------------------------------
        //     WARNING! THE SMS_TYPE_TEST* SMS TYPES DOESN'T DISPATCH ANY SMS
        //     USE THEM ONLY TO CHECK IF YOU CAN REACH THE SKEBBY SERVER
        //  ------------------------------------------------------------------

        // ------------- SMS Classic test dispatch ---------
        // SMS CLASSIC test dispatch with custom alphanumeric sender
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_TEST_CLASSIC,'','John');

        // SMS CLASSIC test dispatch with custom numeric sender
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_TEST_CLASSIC,'393471234567');

        // ------------ SMS Classic Plus test dispatch -----------

        // SMS CLASSIC PLUS test dispatch (with delivery report) with custom alphanumeric sender
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_TEST_CLASSIC_PLUS,'','John');

        // SMS CLASSIC PLUS test dispatch (with delivery report) with custom numeric sender
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_TEST_CLASSIC_PLUS,'393471234567');

        // ------------- SMS Basic test dispatch ----------------
        // $result = $this->skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you? By John', SMS_TYPE_TEST_BASIC);

        // ------------------------------------------------------------------
        //    WARNING! THE SMS_TYPE_TEST* SMS TYPES DOESN'T DISPATCH ANY SMS
        //    USE THEM ONLY TO CHECK IF YOU CAN REACH THE SKEBBY SERVER
        // ------------------------------------------------------------------


        if ($result['status'] == 'success') {
            return true;
//            echo '<b style="color:#8dc63f;">Message Sent!</b><br/>';
//            if (isset($result['remaining_sms'])) {
//                echo '<b>Remaining SMS:</b> ' . $result['remaining_sms'];
//            }
//            if (isset($result['id'])) {
//                echo '<b>ID:</b> ' . $result['id'];
//            }
        }

        // ------------------------------------------------------------------
        // Check the complete documentation at http://www.skebby.com/business/index/send-docs/
        // ------------------------------------------------------------------
        // For eventual errors see http:#www.skebby.com/business/index/send-docs/#errorCodesSection
        // WARNING: in case of error DON'T retry the sending, since they are blocking errors
        // ------------------------------------------------------------------
        if ($result['status'] == 'failed') {
            $code = 0;
            if (isset($result['code'])) {
                $code = $result['code'];
            }
            throw new SmsGatewayException(urldecode($result['message']), $code);
        }


    }

    protected function do_post_request($url, $data, $optional_headers = null)
    {
        if (!function_exists('curl_init')) {
            $params = array(
                'http' => array(
                    'method' => 'POST',
                    'content' => $data
                )
            );
            if ($optional_headers !== null) {
                $params['http']['header'] = $optional_headers;
            }
            $ctx = stream_context_create($params);
            $fp = @fopen($url, 'rb', false, $ctx);
            if (!$fp) {
                return 'status=failed&message=' . NET_ERROR;
            }
            $response = @stream_get_contents($fp);
            if ($response === false) {
                return 'status=failed&message=' . NET_ERROR;
            }
            return $response;
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Generic Client');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_URL, $url);

            if ($optional_headers !== null) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $optional_headers);
            }

            $response = curl_exec($ch);
            curl_close($ch);
            if (!$response) {
                return 'status=failed&message=' . NET_ERROR;
            }
            return $response;
        }
    }

    protected function skebbyGatewaySendSMS($username, $password, $recipients, $text, $sms_type = SMS_TYPE_CLASSIC, $sender_number = '', $sender_string = '', $user_reference = '', $charset = '', $optional_headers = null)
    {
        $url = 'http://gateway.skebby.it/api/send/smseasy/advanced/http.php';

        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }

        switch ($sms_type) {
            case SMS_TYPE_CLASSIC:
            default:
                $method = 'send_sms_classic';
                break;
            case SMS_TYPE_CLASSIC_PLUS:
                $method = 'send_sms_classic_report';
                break;
            case SMS_TYPE_BASIC:
                $method = 'send_sms_basic';
                break;
            case SMS_TYPE_TEST_CLASSIC:
                $method = 'test_send_sms_classic';
                break;
            case SMS_TYPE_TEST_CLASSIC_PLUS:
                $method = 'test_send_sms_classic_report';
                break;
            case SMS_TYPE_TEST_BASIC:
                $method = 'test_send_sms_basic';
                break;
        }

        $parameters = 'method='
            . urlencode($method) . '&'
            . 'username='
            . urlencode($username) . '&'
            . 'password='
            . urlencode($password) . '&'
            . 'text='
            . urlencode($text) . '&'
            . 'recipients[]=' . implode('&recipients[]=', $recipients);

        if ($sender_number != '' && $sender_string != '') {
            parse_str('status=failed&message=' . SENDER_ERROR, $result);
            return $result;
        }
        $parameters .= $sender_number != '' ? '&sender_number=' . urlencode($sender_number) : '';
        $parameters .= $sender_string != '' ? '&sender_string=' . urlencode($sender_string) : '';

        $parameters .= $user_reference != '' ? '&user_reference=' . urlencode($user_reference) : '';


        switch ($charset) {
            case 'UTF-8':
                $parameters .= '&charset=' . urlencode('UTF-8');
                break;
            case '':
            case 'ISO-8859-1':
            default:
                break;
        }

        parse_str($this->do_post_request($url, $parameters, $optional_headers), $result);

        return $result;
    }

    protected function skebbyGatewayGetCredit($username, $password, $charset = '')
    {
        $url = "http://gateway.skebby.it/api/send/smseasy/advanced/http.php";
        $method = "get_credit";

        $parameters = 'method='
            . urlencode($method) . '&'
            . 'username='
            . urlencode($username) . '&'
            . 'password='
            . urlencode($password);

        switch ($charset) {
            case 'UTF-8':
                $parameters .= '&charset=' . urlencode('UTF-8');
                break;
            default:
        }

        parse_str($this->do_post_request($url, $parameters), $result);

        return $result;
    }
}