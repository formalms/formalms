<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningGlossaryterm
 *
 * @ORM\Table(name="learning_glossaryterm")
 * @ORM\Entity
 */
class LearningGlossaryterm
{
    /**
     * @var int
     *
     * @ORM\Column(name="idTerm", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idterm;

    /**
     * @var int
     *
     * @ORM\Column(name="idGlossary", type="integer", nullable=false)
     */
    private $idglossary = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="term", type="string", length=255, nullable=false)
     */
    private $term = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;


}
