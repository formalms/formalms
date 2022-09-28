<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReportScheduleRecipient
 *
 * @ORM\Table(name="learning_report_schedule_recipient")
 * @ORM\Entity
 */
class LearningReportScheduleRecipient
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_report_schedule", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idReportSchedule = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';


}
