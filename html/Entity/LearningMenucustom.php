<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningMenucustom
 *
 * @ORM\Table(name="learning_menucustom")
 * @ORM\Entity
 */
class LearningMenucustom
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="idCustom", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcustom;

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
