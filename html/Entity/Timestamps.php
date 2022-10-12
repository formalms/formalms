<?php 

namespace Formalms\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Timestamps
 *
 * @ORM\HasLifecycleCallbacks()
 * 
 */
trait Timestamps {


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
    */
    private $created_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
    */
    private $updated_at;

    public function setUpdatedAt($date)
    {
        $this->updated_at = $date;
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setCreatedAt($date)
    {
        $this->created_at = $date;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setUpdatedAt(new \DateTime('now'));

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }


}