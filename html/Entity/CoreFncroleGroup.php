<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreFncroleGroup
 *
 * @ORM\Table(name="core_fncrole_group")
 * @ORM\Entity
 */
class CoreFncroleGroup
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_group", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idGroup;


}
