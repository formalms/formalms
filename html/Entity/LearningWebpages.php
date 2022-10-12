<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningWebpages
 *
 * @ORM\Table(name="learning_webpages")
 * @ORM\Entity
 */
class LearningWebpages
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idPages", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idpages;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=200, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=255, nullable=false)
     */
    private $language = '';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="publish", type="boolean", nullable=false)
     */
    private $publish = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="in_home", type="boolean", nullable=false)
     */
    private $inHome = '0';


}
