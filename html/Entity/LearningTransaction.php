<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTransaction
 *
 * @ORM\Table(name="learning_transaction", indexes={@ORM\Index(name="id_user", columns={"id_user"})})
 * @ORM\Entity
 */
class LearningTransaction
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id_transaction", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTransaction;

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $date = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_confirm", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateConfirm = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer", nullable=false)
     */
    private $price = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="payment_status", type="boolean", nullable=false)
     */
    private $paymentStatus = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="course_status", type="boolean", nullable=false)
     */
    private $courseStatus = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=255, nullable=false)
     */
    private $method = '';

    /**
     * @var string
     *
     * @ORM\Column(name="payment_note", type="text", length=65535, nullable=false)
     */
    private $paymentNote;

    /**
     * @var string
     *
     * @ORM\Column(name="course_note", type="text", length=65535, nullable=false)
     */
    private $courseNote;


}
