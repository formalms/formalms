<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTestquestanswer
 *
 * @ORM\Table(name="learning_testquestanswer", indexes={
 *      @ORM\Index(name="idQuest_idx", columns={"idQuest"})
 *  })
 * @ORM\Entity
 */
class LearningTestquestanswer
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idAnswer", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idAnswer;

    /**
     * @var int
     *
     * @ORM\Column(name="idQuest", type="integer", nullable=false)
     */
    private $idQuest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="is_correct", type="integer", nullable=false)
     */
    private $isCorrect = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="string", length=65536, nullable=false)
     */
    private $answer;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=65536, nullable=false)
     */
    private $comment;

    /**
     * @var float
     *
     * @ORM\Column(name="score_correct", type="float", precision=10, scale=0, nullable=false)
     */
    private $scoreCorrect = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="score_incorrect", type="float", precision=10, scale=0, nullable=false)
     */
    private $scoreIncorrect = '0';


}
