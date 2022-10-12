<?php



namespace Formalms\Entity;

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
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_quest", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idQuest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_answer", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idAnswer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="extra_info", type="text", length=65535, nullable=false)
     */
    private $extraInfo;


}
