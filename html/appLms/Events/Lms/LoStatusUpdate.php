<?php

namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class LoStatusUpdate extends Event {
    
    const EVENT_NAME = 'lms.lo.status.update';
    const CREATE_TRACK = 'create';
    const UPDATE_TRACK = 'update';

    /**
     * @var
     */

    protected $user;
    protected $objectType;
    protected $status;
    protected $date;
    protected $reference;
    protected $trackType;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

        /**
     * @return mixed
     */
     public function getObjectType()
     {
         return $this->objectType;
     }
 
     /**
      * @param mixed $user
      */
     public function setObjectType($objectType)
     {
         $this->objectType = $objectType;
     }

         /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

        /**
     * @return mixed
     */
     public function getDate()
     {
         return $this->date;
     }
 
     /**
      * @param mixed $date
      */
     public function setDate($date)
     {
         $this->date = $date;
     }


        /**
     * @return mixed
     */
     public function getReference()
     {
         return $this->reference;
     }
 
     /**
      * @param mixed $reference
      */
     public function setReference($reference)
     {
         $this->reference = $reference;
     }

    /**
     * @return mixed
     */
     public function getTrackType()
     {
         return $this->trackType;
     }
 
     /**
      * @param mixed $UpdateType
      */
     public function setTrackType($trackType)
     {
         $this->trackType = $trackType;
     }
}