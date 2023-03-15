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

namespace appLms\Events\Lms;

use Symfony\Contracts\EventDispatcher\Event;

class TestUpdateModalityEvent extends Event
{
    public const EVENT_NAME = 'lms.test.modality.update';

    /**
     * @var
     */
    protected $queryString;

    /**
     * @var
     */
    protected $postVars;

    /**
     * @var
     */
    protected $idTest;

    public function __construct($idTest, $queryString)
    {
        $this->idTest = $idTest;
        $this->queryString = $queryString;
    }

    /**
     * @param mixed $idTest
     */
    public function setIdTest($idTest)
    {
        $this->idTest = $idTest;
    }

    /**
     * @return mixed
     */
    public function getIdTest()
    {
        return $this->idTest;
    }

    /**
     * @param mixed $postVars
     */
    public function setPostVars($postVars)
    {
        $this->postVars = $postVars;
    }

    /**
     * @return mixed
     */
    public function getPostVars()
    {
        return $this->postVars;
    }

    /**
     * @return mixed
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param mixed $queryString
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'queryString' => $this->queryString,
            'postVars' => $this->postVars,
            'idTest' => $this->idTest,
        ];
    }
}
