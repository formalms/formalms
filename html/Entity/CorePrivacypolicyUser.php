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


}
