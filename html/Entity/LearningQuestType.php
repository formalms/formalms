<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningQuestType
 *
 * @ORM\Table(name="learning_quest_type", indexes={
 *      @ORM\Index(name="type_quest_idx", columns={"type_quest"})})
 * })
 * @ORM\Entity
 */
class LearningQuestType
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
     * @var string
     *
     * @ORM\Column(name="type_quest", type="string", length=255, nullable=false)
     
     */
    private $typeQuest = '';

    /**
     * @var string
     *
     * @ORM\Column(name="type_file", type="string", length=255, nullable=false)
     */
    private $typeFile = '';

    /**
     * @var string
     *
     * @ORM\Column(name="type_class", type="string", length=255, nullable=false)
     */
    private $typeClass = '';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';


}
