<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPolltrackAnswer
 *
 * @ORM\Table(name="learning_polltrack_answer")
 * @ORM\Entity
 */
class LearningPolltrackAnswer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_track", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idTrack = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_quest", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idQuest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_answer", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idAnswer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="more_info", type="text", length=0, nullable=false)
     */
    private $moreInfo;


}
