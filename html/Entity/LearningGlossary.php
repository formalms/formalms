<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningGlossary
 *
 * @ORM\Table(name="learning_glossary")
 * @ORM\Entity
 */
class LearningGlossary
{

    use Timestamps;


    /**
     * @var int
     *
     * @ORM\Column(name="idGlossary", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idglossary;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=false)
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
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';


}
