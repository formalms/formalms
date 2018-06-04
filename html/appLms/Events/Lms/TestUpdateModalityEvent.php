<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class TestUpdateModalityEvent extends Event
{
    const EVENT_NAME = 'lms.test.modality.update';

    /**
     * @var $queryString
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

    public function __construct($idTest,$queryString)
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