<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningMenucustom
 *
 * @ORM\Table(name="learning_menucustom")
 * @ORM\Entity
 */
class LearningMenucustom
{
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
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;


}
