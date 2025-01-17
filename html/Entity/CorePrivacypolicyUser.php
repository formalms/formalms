<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorePrivacypolicyUser
 *
 * @ORM\Table(name="core_privacypolicy_user")
 * @ORM\Entity
 */
class CorePrivacypolicyUser
{

    use Timestamps;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_policy", type="integer", nullable=false)
     */
    private $idPolicy;

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     */
    private $idst;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="accept_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $acceptDate;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reject_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $rejectDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): CorePrivacypolicyUser
    {
        $this->id = $id;
        return $this;
    }

    public function getIdPolicy(): int
    {
        return $this->idPolicy;
    }

    public function setIdPolicy(int $idPolicy): CorePrivacypolicyUser
    {
        $this->idPolicy = $idPolicy;
        return $this;
    }

    public function getIdst(): int
    {
        return $this->idst;
    }

    public function setIdst(int $idst): CorePrivacypolicyUser
    {
        $this->idst = $idst;
        return $this;
    }

    public function getAcceptDate(): \DateTime
    {
        return $this->acceptDate;
    }

    public function setAcceptDate(\DateTime $acceptDate): CorePrivacypolicyUser
    {
        $this->acceptDate = $acceptDate;
        return $this;
    }

    public function getRejectDate(): \DateTime
    {
        return $this->rejectDate;
    }

    public function setRejectDate(\DateTime $rejectDate): CorePrivacypolicyUser
    {
        $this->rejectDate = $rejectDate;
        return $this;
    }
}
