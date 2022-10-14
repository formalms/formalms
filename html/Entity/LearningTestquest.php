<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTestquest
 *
 * @ORM\Table(name="learning_testquest")
 * @ORM\Entity
 */
class LearningTestquest
{
    /**
     * @var int
     *
     * @ORM\Column(name="idQuest", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idquest;

    /**
     * @var int
     *
     * @ORM\Column(name="idTest", type="integer", nullable=false)
     */
    private $idtest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     */
    private $idcategory = '0';

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
     * @ORM\Column(name="difficult", type="integer", nullable=false, options={"default"="3"})
     */
    private $difficult = 3;

    /**
     * @var int
     *
     * @ORM\Column(name="time_assigned", type="integer", nullable=false)
     */
    private $timeAssigned = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="page", type="integer", nullable=false)
     */
    private $page = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=false)
     */
    private $shuffle = '0';


}
