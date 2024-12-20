<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreFncroleGroup
 *
 * @ORM\Table(name="core_fncrole_group")
 * @ORM\Entity
 */
class CoreFncroleGroup
{

    use Timestamps;
    /**
     * @var int
     *
     * @ORM\Column(name="id_group", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idGroup;


}
