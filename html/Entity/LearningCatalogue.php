<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCatalogue
 *
 * @ORM\Table(name="learning_catalogue")
 * @ORM\Entity
 */
class LearningCatalogue
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idCatalogue", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcatalogue;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
     */
    private $description;


}
