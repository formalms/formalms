<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetenceCourse
 *
 * @ORM\Table(name="learning_competence_course", indexes={
 *     @ORM\Index(name="id_competence_idx", columns={"id_competence"}),
 *     @ORM\Index(name="id_course_idx", columns={"id_course"})
 * })
 * @ORM\Entity
 */
class LearningCompetenceCourse
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
     
     */
    private $idCompetence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false, options={"unsigned"=true})
     
     */
    private $idCourse = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="score", type="float", precision=10, scale=0, nullable=false)
     */
    private $score = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="retraining", type="boolean", nullable=false)
     */
    private $retraining = '0';


}
