<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetenceTrack
 *
 * @ORM\Table(name="learning_competence_track")
 * @ORM\Entity
 */
class LearningCompetenceTrack
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_track", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTrack;

    /**
     * @var int
     *
     * @ORM\Column(name="id_competence", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idCompetence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idUser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     */
    private $idCourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="assigned_by", type="integer", nullable=false)
     */
    private $assignedBy = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="operation", type="string", length=255, nullable=false)
     */
    private $operation = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_assignment", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateAssignment = '0000-00-00 00:00:00';

    /**
     * @var float
     *
     * @ORM\Column(name="score_assigned", type="float", precision=10, scale=0, nullable=false)
     */
    private $scoreAssigned = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="score_total", type="float", precision=10, scale=0, nullable=false)
     */
    private $scoreTotal = '0';


}
