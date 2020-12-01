<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;


class TestShowScoreCategoryEvent extends Event
{
    const EVENT_NAME = 'lms.test.complete.show_category';

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
    protected $lang;

    /**
     * @var
     */
    protected $acl_man;

    /**
     * @var string
     */
    protected $scoreCategoryTable;

    /**
     * @var array
     */
    protected $scoreCategoryData;

    public function __construct($object_test,$user_id,$acl_man)
    {
        $this->test = $object_test;
        $this->userId = $user_id;
        $this->acl_man = $acl_man;
        $this->scoreCategoryData = [];
    }

    /**
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * @param mixed $test
     * @return TestShowScoreCategoryEvent
     */
    public function setTest($test)
    {
        $this->test = $test;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     * @return TestShowScoreCategoryEvent
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param mixed $lang
     * @return TestShowScoreCategoryEvent
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAclMan()
    {
        return $this->acl_man;
    }

    /**
     * @param mixed $acl_man
     * @return TestShowScoreCategoryEvent
     */
    public function setAclMan($acl_man)
    {
        $this->acl_man = $acl_man;
        return $this;
    }

    /**
     * @return string
     */
    public function getScoreCategoryTable()
    {
        return $this->scoreCategoryTable;
    }

    /**
     * @param string $scoreCategoryTable
     * @return TestShowScoreCategoryEvent
     */
    public function setScoreCategoryTable( $scoreCategoryTable)
    {
        $this->scoreCategoryTable = $scoreCategoryTable;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getScoreCategoryData()
    {
        return $this->scoreCategoryData;
    }

    /**
     * @param mixed $scoreCategoryData
     * @return TestShowScoreCategoryEvent
     */
    public function setScoreCategoryData($scoreCategoryData)
    {
        $this->scoreCategoryData = $scoreCategoryData;
        return $this;
    }
}