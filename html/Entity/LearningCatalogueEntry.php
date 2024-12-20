<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCatalogueEntry
 *
 * @ORM\Table(name="learning_catalogue_entry", indexes={
 *     @ORM\Index(name="type_of_entry_idx", columns={"type_of_entry"}),
 *     @ORM\Index(name="id_catalogue_idx", columns={"idCatalogue"}),
 *     @ORM\Index(name="id_entry_idx", columns={"idEntry"})
 * })
 * @ORM\Entity
 */
class LearningCatalogueEntry
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type_of_entry", type="string", length=0, nullable=false, options={"default"="course"})
     
     */
    private $typeOfEntry = 'course';

    /**
     * @var int
     *
     * @ORM\Column(name="idCatalogue", type="integer", nullable=false)
     
     */
    private $idcatalogue = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idEntry", type="integer", nullable=false)
     
     */
    private $identry = '0';


}
