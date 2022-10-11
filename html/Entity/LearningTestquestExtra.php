<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTestquestExtra
 *
 * @ORM\Table(name="learning_testquest_extra", indexes={
 *      @ORM\Index(name="id_quest_idx", columns={"idQuest"}),
 *      @ORM\Index(name="id_answer_idx", columns={"idAnswer"})
 * })
 * @ORM\Entity
 */
class LearningTestquestExtra
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
     * @ORM\Column(name="idQuest", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idquest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idAnswer", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idanswer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="extra_info", type="text", length=65535, nullable=false)
     */
    private $extraInfo;


}
