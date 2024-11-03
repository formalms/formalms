<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPoll
 *
 * @ORM\Table(name="learning_poll")
 * @ORM\Entity
 */
class LearningPoll
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id_poll", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPoll;

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';

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


}
