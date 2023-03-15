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

namespace appLms\Events\Transaction;

use Symfony\Contracts\EventDispatcher\Event;

class TransactionPaidEvent extends Event
{
    public const EVENT_NAME = 'transaction.paid';

    /**
     * @var
     */
    protected $trans;

    /**
     * @var
     */
    protected $res;

    public function __construct($trans)
    {
        $this->trans = $trans;
        $this->res = true;
    }

    /**
     * @param mixed $trans
     */
    public function setTrans($trans)
    {
        $this->trans = $trans;
    }

    /**
     * @return mixed
     */
    public function getTrans()
    {
        return $this->trans;
    }

    /**
     * @param mixed $res
     */
    public function setRes($res)
    {
        $this->res = $res;
    }

    /**
     * @return mixed
     */
    public function getRes()
    {
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
