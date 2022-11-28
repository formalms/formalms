<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPollquestanswer
 *
 * @ORM\Table(name="learning_pollquestanswer")
 * @ORM\Entity
 */
class LearningPollquestanswer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_answer", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idAnswer;

    /**
     * @var int
     *
     * @ORM\Column(name="id_quest", type="integer", nullable=false)
     */
    private $idQuest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="text", length=65535, nullable=false)
     */
    private $answer;


}
