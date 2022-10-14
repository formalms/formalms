<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetenceRequired
 *
 * @ORM\Table(name="learning_competence_required", indexes={
 *     @ORM\Index(name="idst_idx", columns={"idst"}),
 *     @ORM\Index(name="id_competence_idx", columns={"id_competence"})
 * })
 * @ORM\Entity
 */
class LearningCompetenceRequired
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_competence", type="integer", nullable=false, options={"unsigned"=true})
     
     */
    private $idCompetence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false, options={"unsigned"=true})
     
     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type_of", type="string", length=255, nullable=false)
     */
    private $typeOf = '';


}
