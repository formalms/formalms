<?php
namespace appLms\Events\Lms;

use Behat\Mink\Exception\Exception;
use Symfony\Component\EventDispatcher\Event;
use appCore\lib\Sms\SmsGatewayManager;

class TestCompletedEvent extends Event
{
    const EVENT_NAME = 'lms.test.complete';

    /**
     * @var
     */
    protected $test;

    /**
     * @var
     */
    protected $userId;

    /**
     * @var
     */
    protected $userPhoneNumber;

    /**
     * @var
     */
    protected $lang;

    /**
     * @var
     */
    protected $acl_man;

    /**
     * @var
     */
    protected $testScore;

    /**
     * @var
     */
    protected $testDate;

    public function __construct($object_test,$user_id,$acl_man)
    {
        $this->test = $object_test;
        $this->userId = $user_id;
        $this->acl_man = $acl_man;
    }

    /**
     * @param mixed $test
     */
    public function setTest($test)
    {
        $this->test = $test;
    }
    /**
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }


    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userPhoneNumber
     */
    public function setUserPhoneNumber($userPhoneNumber)
    {
        $this->userPhoneNumber = $userPhoneNumber;
    }

    /**
     * @return mixed
     */
    public function getUserPhoneNumber()
    {
        return $this->userPhoneNumber;
    }

    public function sendMessage($messageText) {

        require_once(_base_ . '/appCore/lib/Sms/SmsGatewayManager.php');

        try {
            return \SmsGatewayManager::send(array($this->userPhoneNumber), strip_tags($messageText));

        }
        catch (SmsGatewayException $e) {
            return false;
        }
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param mixed $acl_man
     */
    public function setAclMan($acl_man)
    {
        $this->acl_man = $acl_man;
    }

    /**
     * @return mixed
     */
    public function getAclMan()
    {
        return $this->acl_man;
    }

    /**
     * @param mixed $testScore
     */
    public function setTestScore($testScore)
    {
        $this->testScore = $testScore;
    }

    /**
     * @return mixed
     */
    public function getTestScore()
    {
        return $this->testScore;
    }

    /**
     * @param mixed $testDate
     */
    public function setTestDate($testDate)
    {
        $this->testDate = $testDate;
    }

    /**
     * @return mixed
     */
    public function getTestDate()
    {
        return $this->testDate;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'test' => $this->test,
            'userId' => $this->userId,
            'userPhoneNumber' => $this->userPhoneNumber,
            'lang' => $this->lang,
            'acl_man' => $this->acl_man,
            'testScore' => $this->testScore,
        ];
    }
}