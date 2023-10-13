<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAdvice
 *
 * @ORM\Table(name="learning_advice")
 * @ORM\Entity
 */
class LearningAdvice
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idAdvice", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idadvice;

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="posted", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $posted = null;

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

    /**
     * @var bool
     *
     * @ORM\Column(name="important", type="boolean", nullable=false)
     */
    private $important = '0';


}
