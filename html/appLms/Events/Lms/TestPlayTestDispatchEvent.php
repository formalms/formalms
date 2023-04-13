<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace appLms\Events\Lms;

use Symfony\Contracts\EventDispatcher\Event;

class TestPlayTestDispatchEvent extends Event
{
    public const EVENT_NAME = 'lms.test.play.test.dispatch';

    public const DISPATCH_ACTION_PLAY = 'action_play';

    public const DISPATCH_ACTION_DELETE_AND_BEGIN = 'action_delete_and_begin';

    public const DISPATCH_ACTION_RESTART = 'action_restart';

    public const DISPATCH_ACTION_TEST_SAVE_KEEP = 'action_test_save_keep';

    public const DISPATCH_ACTION_SHOW_RESULT = 'action_show_result';

    public const DISPATCH_ACTION_TIME_ELAPSED = 'action_time_elapsed';

    protected $objectTest;

    protected $idParam;
    /**
     * @var \FormaLms\lib\FormaUser
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

    public function __construct($user, $object_test, $id_param, $id_test, $id_track)
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
    public function getObjectTest()
    {
        return $this->objectTest;
    }

    /**
     * @return mixed
     */
    public function getIdParam()
    {
        return $this->idParam;
    }

    /**
     * @return \FormaLms\lib\FormaUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getIdTest()
    {
        return $this->idTest;
    }

    /**
     * @return mixed
     */
    public function getIdTrack()
    {
        return $this->idTrack;
    }

    /**
     * @return string
     */
    public function getDispatchAction()
    {
        return $this->dispatchAction;
    }

    /**
     * @param string $dispatchAction
     *
     * @return TestPlayTestDispatchEvent
     */
    public function setDispatchAction($dispatchAction)
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
