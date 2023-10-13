<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLink
 *
 * @ORM\Table(name="learning_link")
 * @ORM\Entity
 */
class LearningLink
{

    use Timestamps;


    /**
     * @var int
     *
     * @ORM\Column(name="idLink", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idlink;

    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     */
    private $idcategory = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="link_address", type="string", length=255, nullable=false)
     */
    private $linkAddress = '';

    /**
     * @var string
     *
     * @ORM\Column(name="keyword", type="string", length=65536, nullable=false)
     */
    private $keyword;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';


}
