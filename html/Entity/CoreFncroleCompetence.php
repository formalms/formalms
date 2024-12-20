<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * CoreFncroleCompetence
 *
 * @ORM\Table(name="core_fncrole_competence", indexes={
 *     @ORM\Index(name="id_competence_idx", columns={"id_competence"}),
 *     @ORM\Index(name="id_fncrole_idx", columns={"id_fncrole"})
 * })
 * @ORM\Entity
 */
class CoreFncroleCompetence
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
     * @ORM\Column(name="id_competence", type="integer", nullable=false, options={"unsigned"=true})

     */
    private $idCompetence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="score", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $score = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="expiration", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $expiration = '0';


}
