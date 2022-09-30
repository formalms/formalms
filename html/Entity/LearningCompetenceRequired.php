<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetenceRequired
 *
 * @ORM\Table(name="learning_competence_required")
 * @ORM\Entity
 */
class LearningCompetenceRequired
{
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
     * @ORM\Column(name="id_competence", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCompetence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type_of", type="string", length=255, nullable=false)
     */
    private $typeOf = '';


}
