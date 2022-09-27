<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPollquest
 *
 * @ORM\Table(name="learning_pollquest")
 * @ORM\Entity
 */
class LearningPollquest
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_quest", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idQuest;

    /**
     * @var int
     *
     * @ORM\Column(name="id_poll", type="integer", nullable=false)
     */
    private $idPoll = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_category", type="integer", nullable=false)
     */
    private $idCategory = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type_quest", type="string", length=255, nullable=false)
     */
    private $typeQuest = '';

    /**
     * @var string
     *
     * @ORM\Column(name="title_quest", type="text", length=65535, nullable=false)
     */
    private $titleQuest;

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="page", type="integer", nullable=false, options={"default"="1"})
     */
    private $page = 1;


}
