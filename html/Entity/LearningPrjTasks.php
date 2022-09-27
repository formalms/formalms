<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPrjTasks
 *
 * @ORM\Table(name="learning_prj_tasks")
 * @ORM\Entity
 */
class LearningPrjTasks
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="pid", type="integer", nullable=false)
     */
    private $pid = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="tprog", type="boolean", nullable=false)
     */
    private $tprog = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="tname", type="string", length=255, nullable=false)
     */
    private $tname = '';

    /**
     * @var string
     *
     * @ORM\Column(name="tdesc", type="text", length=65535, nullable=false)
     */
    private $tdesc;


}
