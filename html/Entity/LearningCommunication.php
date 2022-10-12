<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCommunication
 *
 * @ORM\Table(name="learning_communication")
 * @ORM\Entity
 */
class LearningCommunication
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id_comm", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idComm;

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

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_date", type="date", nullable=false, options={"default"="0000-00-00"})
     */
    private $publishDate = '0000-00-00';

    /**
     * @var string
     *
     * @ORM\Column(name="type_of", type="string", length=15, nullable=false)
     */
    private $typeOf = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_resource", type="integer", nullable=false)
     */
    private $idResource = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_category", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idCategory = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idCourse = '0';


}
