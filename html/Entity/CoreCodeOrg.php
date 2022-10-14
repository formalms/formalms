<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCodeOrg
 *
 * @ORM\Table(name="core_code_org", indexes={
 *     @ORM\Index(name="id_codegroup_idx", columns={"idCodeGroup"}),
 *     @ORM\Index(name="id_org_idx", columns={"idOrg"})
 * })
 * @ORM\Entity
 */
class CoreCodeOrg
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
     * @ORM\Column(name="idCodeGroup", type="integer", nullable=false)
     
     */
    private $idcodegroup = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idOrg", type="integer", nullable=false)
     
     */
    private $idorg = '0';


}
