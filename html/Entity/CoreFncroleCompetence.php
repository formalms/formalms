<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreFncroleCompetence
 *
 * @ORM\Table(name="core_fncrole_competence")
 * @ORM\Entity
 */
class CoreFncroleCompetence
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_fncrole", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idFncrole = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_competence", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
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
