<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreFncrole
 *
 * @ORM\Table(name="core_fncrole", indexes={
 *     @ORM\Index(name="id_fncrole_idx", columns={"id_fncrole"}),
 *     @ORM\Index(name="id_group_idx", columns={"id_group"})
 * })
 * @ORM\Entity
 */
class CoreFncrole
{

    use Timestamps;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_fncrole", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idFncrole = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_group", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idGroup = '0';


}
