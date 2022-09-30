<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCatalogueEntry
 *
 * @ORM\Table(name="learning_catalogue_entry")
 * @ORM\Entity
 */
class LearningCatalogueEntry
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type_of_entry", type="string", length=0, nullable=false, options={"default"="course"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $typeOfEntry = 'course';

    /**
     * @var int
     *
     * @ORM\Column(name="idCatalogue", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcatalogue = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idEntry", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $identry = '0';


}
