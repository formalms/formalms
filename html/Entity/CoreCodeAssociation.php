<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCodeAssociation
 *
 * @ORM\Table(name="core_code_association", indexes={
 *     @ORM\Index(name="code_idx", columns={"code"}),
 *     @ORM\Index(name="id_user_idx", columns={"idUser"})
 * })
 * @ORM\Entity
 */
class CoreCodeAssociation
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $code = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $iduser = '0';


}
