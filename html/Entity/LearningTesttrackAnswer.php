<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTesttrackAnswer
 *
 * @ORM\Table(name="learning_testtrack_answer", indexes={
 *      @ORM\Index(name="number_time_idx", columns={"number_time"}),
 *      @ORM\Index(name="id_track_idx", columns={"idTrack"}),
 *      @ORM\Index(name="id_quest_idx", columns={"idQuest"}),
 *      @ORM\Index(name="id_answer_idx", columns={"idanswer"})
 * })
 * @ORM\Entity
 */
class LearningTesttrackAnswer
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
     * @var bool
     *
     * @ORM\Column(name="number_time", type="boolean", nullable=false, options={"default"="1"})
     
     */
    private $numberTime = true;

    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     
     */
    private $idtrack = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idQuest", type="integer", nullable=false)
     
     */
    private $idquest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idAnswer", type="integer", nullable=false)
     
     */
    private $idanswer = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="score_assigned", type="float", precision=10, scale=0, nullable=false)
     */
    private $scoreAssigned = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="more_info", type="text", length=0, nullable=false)
     */
    private $moreInfo;

    /**
     * @var bool
     *
     * @ORM\Column(name="manual_assigned", type="boolean", nullable=false)
     */
    private $manualAssigned = '0';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="user_answer", type="boolean", nullable=true)
     */
    private $userAnswer = '0';


}
