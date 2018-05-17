<?php

namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class TestPlayTestDispatchEvent extends Event
{
    const EVENT_NAME = 'lms.test.play.test.dispatch';
    
    const DISPATCH_ACTION_PLAY = "action_play";
    
    const DISPATCH_ACTION_DELETE_AND_BEGIN = "action_delete_and_begin";
    
    const DISPATCH_ACTION_RESTART = "action_restart";
    
    const DISPATCH_ACTION_TEST_SAVE_KEEP = "action_test_save_keep";
    
    const DISPATCH_ACTION_SHOW_RESULT = "action_show_result";
    
    const DISPATCH_ACTION_TIME_ELAPSED = "action_time_elapsed";
    
    protected $objectTest;
    
    protected $idParam;
    /**
     * @var \DoceboUser
     */
    protected $user;
    
    /**
     * @var int
     */
    protected $idTest;
    
    /**
     * @var int
     */
    protected $idTrack;
    
    /**
     * @var string
     */
    protected $dispatchAction;
    
    
    public function __construct ($user , $object_test , $id_param , $id_test , $id_track)
    {
        $this->user = $user;
        $this->objectTest = $object_test;
        $this->idParam = $id_param;
        $this->idTest = $id_test;
        $this->idTrack = $id_track;
    }
    
    /**
     * @return mixed
     */
    public function getObjectTest ()
    {
        return $this->objectTest;
    }
    
    /**
     * @return mixed
     */
    public function getIdParam ()
    {
        return $this->idParam;
    }
    
    /**
     * @return \DoceboUser
     */
    public function getUser ()
    {
        return $this->user;
    }
    
    /**
     * @return mixed
     */
    public function getIdTest ()
    {
        return $this->idTest;
    }
    
    /**
     * @return mixed
     */
    public function getIdTrack ()
    {
        return $this->idTrack;
    }
    
    /**
     * @return string
     */
    public function getDispatchAction ()
    {
        return $this->dispatchAction;
    }
    
    /**
     * @param string $dispatchAction
     *
     * @return TestPlayTestDispatchEvent
     */
    public function setDispatchAction ($dispatchAction)
    {
        $this->dispatchAction = $dispatchAction;
        
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'objectTest' => $this->objectTest,
            'idParam' => $this->idParam,
            'user' => $this->user,
            'idTest' => $this->idTest,
            'idTrack' => $this->idTrack,
            'dispatchAction' => $this->dispatchAction,
        ];
    }
}
