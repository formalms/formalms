<?php

namespace appLms\Events\Lms;

use Behat\Mink\Exception\Exception;
use Symfony\Component\EventDispatcher\Event;

class CourseReportDetailEvent extends Event
{
    const EVENT_NAME = 'lms.coursereport.detail';
    
    /**
     * @var
     */
    protected $testObj;
    
    /**
     * @var
     */
    protected $userId;
    
    /**
     * @var
     */
    protected $testsScore;
    
    /**
     * @var
     */
    protected $info_report;
    
    /**
     * @var array
     */
    protected $values = [];
    
    /**
     * @var
     */
    protected $aclMan;
    
    /**
     * @var
     */
    protected $userInfo;
    
    
    /**
     * CourseReportDetailEvent constructor.
     * @param $testObj
     * @param $tests_score
     * @param $info_report
     * @param $idst_user
     */
    public function __construct ($testObj , $tests_score , $info_report , $idst_user , $acl_man , $user_info)
    {
        $this->testObj = $testObj;
        $this->testsScore = $tests_score;
        $this->info_report = $info_report;
        $this->userId = $idst_user;
        $this->aclMan = $acl_man;
        $this->userInfo = $user_info;
    }
    
    /**
     * @return mixed
     */
    public function getTestObj ()
    {
        return $this->testObj;
    }
    
    /**
     * @param mixed $testObj
     *
     * @return CourseReportDetailEvent
     */
    public function setTestObj ($testObj)
    {
        $this->testObj = $testObj;
    }
    
    /**
     * @return mixed
     */
    public function getUserId ()
    {
        return $this->userId;
    }
    
    /**
     * @param mixed $userId
     *
     * @return CourseReportDetailEvent
     */
    public function setUserId ($userId)
    {
        $this->userId = $userId;
        
        return $this;
    }
    
    
    /**
     * @return mixed
     */
    public function getTestsScore ()
    {
        return $this->testsScore;
    }
    
    /**
     * @param mixed $testsScore
     *
     * @return CourseReportDetailEvent
     */
    public function setTestsScore ($testsScore)
    {
        $this->testsScore = $testsScore;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getInfoReport ()
    {
        return $this->info_report;
    }
    
    /**
     * @param mixed $info_report
     *
     * @return CourseReportDetailEvent
     */
    public function setInfoReport ($info_report)
    {
        $this->info_report = $info_report;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getValues ()
    {
        return $this->values;
    }
    
    /**
     * @param array $values
     *
     * @return CourseReportDetailEvent
     */
    public function setValues ($values)
    {
        $this->values = $values;
        
        return $this;
    }
    
    /**
     * @param $value mixed
     * @return CourseReportDetailEvent
     */
    public function addValue ($value)
    {
        $this->values[] = $value;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getAclMan ()
    {
        return $this->aclMan;
    }
    
    /**
     * @param mixed $aclMan
     *
     * @return CourseReportDetailEvent
     */
    public function setAclMan ($aclMan)
    {
        $this->aclMan = $aclMan;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getUserInfo ()
    {
        return $this->userInfo;
    }
    
    /**
     * @param mixed $userInfo
     *
     * @return CourseReportDetailEvent
     */
    public function setUserInfo ($userInfo)
    {
        $this->userInfo = $userInfo;
        
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'testObj' => $this->testObj,
            'userId' => $this->userId,
            'testsScore' => $this->testsScore,
            'info_report' => $this->info_report,
            'values' => $this->values,
            'aclMan' => $this->aclMan,
            'userInfo' => $this->userInfo,
        ];
    }
    
}
