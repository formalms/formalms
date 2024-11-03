<?php



namespace FormaLms\Entity;

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

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var string
     *
     * @ORM\Column(name="extra_info", type="string", length=65536, nullable=false)
     */
    private $extraInfo;


}
