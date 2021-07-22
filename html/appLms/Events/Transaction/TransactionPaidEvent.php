<?php

namespace appLms\Events\Transaction;

use Symfony\Component\EventDispatcher\Event;

class TransactionPaidEvent extends Event {
    
    const EVENT_NAME = 'transaction.paid';

    /**
     * @var
     */
    protected $trans;

    /**
     * @var
     */
    protected $res;

    public function __construct($trans) {
        
        $this->trans = $trans;
        $this->res = true;
    }

    /**
     * @param mixed $trans
     */
    public function setTrans($trans) {
        
        $this->trans = $trans;
    }
    
    /**
     * @return mixed
     */
    public function getTrans() {
        
        return $this->trans;
    }

    /**
     * @param mixed $res
     */
    public function setRes($res) {
        
        $this->res = $res;
    }
    
    /**
     * @return mixed
     */
    public function getRes() {
        
        return $this->res;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'res' => $this->res,
            'trans' => $this->trans,
        ];
    }
}