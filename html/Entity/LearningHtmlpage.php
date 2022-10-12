<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningHtmlpage
 *
 * @ORM\Table(name="learning_htmlpage")
 * @ORM\Entity
 */
class LearningHtmlpage
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idPage", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idpage;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="textof", type="text", length=65535, nullable=false)
     */
    private $textof;

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';


}
