<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningFaq
 *
 * @ORM\Table(name="learning_faq")
 * @ORM\Entity
 */
class LearningFaq
{
    /**
     * @var int
     *
     * @ORM\Column(name="idFaq", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idfaq;

    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     */
    private $idcategory = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="question", type="string", length=255, nullable=false)
     */
    private $question = '';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="keyword", type="text", length=65535, nullable=false)
     */
    private $keyword;

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="text", length=65535, nullable=false)
     */
    private $answer;

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';


}
