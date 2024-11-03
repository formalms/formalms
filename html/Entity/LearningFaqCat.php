<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningFaqCat
 *
 * @ORM\Table(name="learning_faq_cat")
 * @ORM\Entity
 */
class LearningFaqCat
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcategory;

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
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';


}
