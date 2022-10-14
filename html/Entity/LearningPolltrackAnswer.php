<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPolltrackAnswer
 *
 * @ORM\Table(name="learning_polltrack_answer", indexes={
 *      @ORM\Index(name="id_track_idx", columns={"id_track"}),
 *      @ORM\Index(name="id_quest_idx", columns={"id_quest"}),
 *      @ORM\Index(name="id_answer_idx", columns={"id_answer"})
 * })
 * @ORM\Entity
 */
class LearningPolltrackAnswer
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
     * @ORM\Column(name="id_track", type="integer", nullable=false)
     
     */
    private $idTrack = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_quest", type="integer", nullable=false)
     
     */
    private $idQuest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_answer", type="integer", nullable=false)
     
     */
    private $idAnswer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="more_info", type="text", length=0, nullable=false)
     */
    private $moreInfo;


}
