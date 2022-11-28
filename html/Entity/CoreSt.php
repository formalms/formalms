<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreSt
 *
 * @ORM\Table(name="core_st")
 * @ORM\Entity
 */
class CoreSt
{
    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idst;


}
