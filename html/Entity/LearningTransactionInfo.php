<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTransactionInfo
 *
 * @ORM\Table(name="learning_transaction_info", indexes={
 *      @ORM\Index(name="id_transaction_idx", columns={"id_transaction"}),
 *      @ORM\Index(name="id_course_idx", columns={"id_course"}),
 *      @ORM\Index(name="id_date_idx", columns={"id_date"})
 * 
 * })
 * @ORM\Entity
 */
class LearningTransactionInfo
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_transaction", type="integer", nullable=false)
     
     */
    private $idTransaction = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     
     */
    private $idCourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_date", type="integer", nullable=false)
     
     */
    private $idDate = '0';


}
