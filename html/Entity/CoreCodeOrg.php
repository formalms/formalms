<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCodeOrg
 *
 * @ORM\Table(name="core_code_org")
 * @ORM\Entity
 */
class CoreCodeOrg
{
    /**
     * @var int
     *
     * @ORM\Column(name="idCodeGroup", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcodegroup = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idOrg", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idorg = '0';


}
