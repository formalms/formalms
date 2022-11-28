<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAdvice
 *
 * @ORM\Table(name="learning_advice")
 * @ORM\Entity
 */
class LearningAdvice
{
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
     * @ORM\Column(name="posted", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $posted = '0000-00-00 00:00:00';

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
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="important", type="boolean", nullable=false)
     */
    private $important = '0';


}
