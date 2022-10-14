<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReportScheduleRecipient
 *
 * @ORM\Table(name="learning_report_schedule_recipient", indexes={
 *      @ORM\Index(name="id_report_schedule_idx", columns={"id_report_schedule"}),
 *      @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningReportScheduleRecipient
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
     * @ORM\Column(name="id_report_schedule", type="integer", nullable=false, options={"unsigned"=true})
     
     */
    private $idReportSchedule = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false, options={"unsigned"=true})
     
     */
    private $idUser = '0';


}
