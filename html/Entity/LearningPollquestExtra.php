<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPollquestExtra
 *
 * @ORM\Table(name="learning_pollquest_extra", indexes={
 *      @ORM\Index(name="id_quest_idx", columns={"id_quest"}),
 *      @ORM\Index(name="id_answer_idx", columns={"id_answer"})
 * })
 * @ORM\Entity
 */
class LearningPollquestExtra
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
     * @ORM\Column(name="extra_info", type="string", length=65536, nullable=false)
     */
    private $extraInfo;


}
