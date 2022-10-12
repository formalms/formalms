<?php



namespace Formalms\Entity;

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
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idst_user", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
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
     * @ORM\Column(name="request_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $requestDate = '0000-00-00 00:00:00';


}
