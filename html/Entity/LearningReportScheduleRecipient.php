<?php



namespace Formalms\Entity;

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
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_report_schedule", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idReportSchedule = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';


}
