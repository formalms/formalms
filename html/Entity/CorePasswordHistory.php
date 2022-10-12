<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorePasswordHistory
 *
 * @ORM\Table(name="core_password_history", indexes={
 *      @ORM\Index(name="pwd_date", columns={"pwd_date"}),
 *      @ORM\Index(name="idst_user_idx", columns={"idst_user"})
 * })
 * @ORM\Entity
 */
class CorePasswordHistory
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
     * @var \DateTime
     *
     * @ORM\Column(name="pwd_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $pwdDate = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="passw", type="string", length=100, nullable=false)
     */
    private $passw = '';

    /**
     * @var int
     *
     * @ORM\Column(name="changed_by", type="integer", nullable=false)
     */
    private $changedBy = '0';


}
