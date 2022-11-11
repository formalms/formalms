<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningForumthread
 *
 * @ORM\Table(name="learning_forumthread")
 * @ORM\Entity
 */
class LearningForumthread
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idThread", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idthread;

    /**
     * @var int
     *
     * @ORM\Column(name="id_edition", type="integer", nullable=false)
     */
    private $idEdition = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idForum", type="integer", nullable=false)
     */
    private $idforum = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="posted", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $posted = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="num_post", type="integer", nullable=false)
     */
    private $numPost = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="num_view", type="integer", nullable=false)
     */
    private $numView = '0';

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
     * @var bool
     *
     * @ORM\Column(name="erased", type="boolean", nullable=false)
     */
    private $erased = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="emoticons", type="string", length=255, nullable=false)
     */
    private $emoticons = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="rilevantForum", type="boolean", nullable=false)
     */
    private $rilevantforum = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="privateThread", type="boolean", nullable=false)
     */
    private $privatethread = '0';


}
