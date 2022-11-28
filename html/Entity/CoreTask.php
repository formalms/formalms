<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreTask
 *
 * @ORM\Table(name="core_task")
 * @ORM\Entity
 */
class CoreTask
{
    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $sequence;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description = '';

    /**
     * @var string
     *
     * @ORM\Column(name="conn_source", type="string", length=50, nullable=false)
     */
    private $connSource = '';

    /**
     * @var string
     *
     * @ORM\Column(name="conn_destination", type="string", length=50, nullable=false)
     */
    private $connDestination = '';

    /**
     * @var string
     *
     * @ORM\Column(name="schedule_type", type="string", length=0, nullable=false, options={"default"="at"})
     */
    private $scheduleType = 'at';

    /**
     * @var string
     *
     * @ORM\Column(name="schedule", type="string", length=50, nullable=false)
     */
    private $schedule = '';

    /**
     * @var string
     *
     * @ORM\Column(name="import_type", type="string", length=50, nullable=false)
     */
    private $importType = '';

    /**
     * @var string
     *
     * @ORM\Column(name="map", type="text", length=65535, nullable=false)
     */
    private $map;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_execution", type="datetime", nullable=true)
     */
    private $lastExecution;


}
