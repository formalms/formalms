<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreFncrole
 *
 * @ORM\Table(name="core_fncrole")
 * @ORM\Entity
 */
class CoreFncrole
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_fncrole", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFncrole = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_group", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idGroup = '0';


}
