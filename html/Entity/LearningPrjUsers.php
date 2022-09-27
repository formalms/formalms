<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPrjUsers
 *
 * @ORM\Table(name="learning_prj_users")
 * @ORM\Entity
 */
class LearningPrjUsers
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
     * @var int
     *
     * @ORM\Column(name="userid", type="integer", nullable=false)
     */
    private $userid = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="flag", type="boolean", nullable=false)
     */
    private $flag = '0';


}
