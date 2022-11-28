<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCommunicationAccess
 *
 * @ORM\Table(name="learning_communication_access")
 * @ORM\Entity
 */
class LearningCommunicationAccess
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_comm", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idComm = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '0';


}
