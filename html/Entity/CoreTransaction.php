<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreTransaction
 *
 * @ORM\Table(name="core_transaction", indexes={@ORM\Index(name="id_user", columns={"id_user"})})
 * @ORM\Entity
 */
class CoreTransaction
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id_trans", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTrans;

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=10, nullable=false)
     */
    private $location = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $dateCreation = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_activated", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $dateActivated = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="paid", type="boolean", nullable=false)
     */
    private $paid = '0';


}
