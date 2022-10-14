<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTestquestanswerAssociate
 *
 * @ORM\Table(name="learning_testquestanswer_associate")
 * @ORM\Entity
 */
class LearningTestquestanswerAssociate
{
    /**
     * @var int
     *
     * @ORM\Column(name="idAnswer", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idanswer;

    /**
     * @var int
     *
     * @ORM\Column(name="idQuest", type="integer", nullable=false)
     */
    private $idquest = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="text", length=65535, nullable=false)
     */
    private $answer;


}
