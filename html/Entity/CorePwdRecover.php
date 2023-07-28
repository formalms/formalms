<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorePwdRecover
 *
 * @ORM\Table(name="core_pwd_recover", indexes={
 *     @ORM\Index(name="idst_user_idx", columns={"idst_user"})
 * })
 * @ORM\Entity
 */
class CorePwdRecover
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idst_user", type="integer", nullable=false)
     
     */
    private $idstUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="random_code", type="string", length=255, nullable=false)
     */
    private $randomCode = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="request_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $requestDate = null;


}
