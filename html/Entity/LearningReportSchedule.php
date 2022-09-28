<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReportSchedule
 *
 * @ORM\Table(name="learning_report_schedule")
 * @ORM\Entity
 */
class LearningReportSchedule
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_report_schedule", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idReportSchedule;

    /**
     * @var int
     *
     * @ORM\Column(name="id_report_filter", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idReportFilter = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_creator", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idCreator = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="period", type="string", length=255, nullable=false)
     */
    private $period = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $time = '00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $creationDate = '0000-00-00 00:00:00';

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"="1"})
     */
    private $enabled = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_execution", type="datetime", nullable=true)
     */
    private $lastExecution;


}
