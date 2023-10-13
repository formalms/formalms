<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningForum
 *
 * @ORM\Table(name="learning_forum")
 * @ORM\Entity
 */
class LearningForum
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idForum", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idforum;

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="num_thread", type="integer", nullable=false)
     */
    private $numThread = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="num_post", type="integer", nullable=false)
     */
    private $numPost = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="last_post", type="integer", nullable=false)
     */
    private $lastPost = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="locked", type="boolean", nullable=false)
     */
    private $locked = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="emoticons", type="string", length=255, nullable=false)
     */
    private $emoticons = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_threads", type="integer", nullable=true)
     */
    private $maxThreads = '0';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="threads_are_private", type="boolean", nullable=true)
     */
    private $threadsArePrivate = '0';


}
