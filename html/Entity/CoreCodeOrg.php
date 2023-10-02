<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCodeOrg
 *
 * @ORM\Table(name="core_code_org")
 * @ORM\Entity
 */
class CoreCodeOrg
{
    use Timestamps;
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
