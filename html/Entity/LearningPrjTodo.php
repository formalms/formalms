<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPrjTodo
 *
 * @ORM\Table(name="learning_prj_todo")
 * @ORM\Entity
 */
class LearningPrjTodo
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
     * @var string
     *
     * @ORM\Column(name="ttitle", type="string", length=255, nullable=false)
     */
    private $ttitle = '';

    /**
     * @var string
     *
     * @ORM\Column(name="ttxt", type="text", length=65535, nullable=false)
     */
    private $ttxt;


}
