<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningQuestType
 *
 * @ORM\Table(name="learning_quest_type")
 * @ORM\Entity
 */
class LearningQuestType
{
    /**
     * @var string
     *
     * @ORM\Column(name="type_quest", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
