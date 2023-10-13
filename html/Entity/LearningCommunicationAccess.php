<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCommunicationAccess
 *
 * @ORM\Table(name="learning_communication_access")
 * @ORM\Entity
 */
class LearningCommunicationAccess
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id_comm", type="integer", nullable=false)
     * @ORM\Id
     
     */
    private $idComm;

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     * @ORM\Id
     
     */
    private $idst = '0';


}
