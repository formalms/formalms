<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCourseDateDay
 *
 * @ORM\Table(name="learning_course_date_day", indexes={@ORM\Index(name="id_day_date", columns={"id_day", "id_date"})})
 * @ORM\Entity
 */
class LearningCourseDateDay
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_day", type="integer", nullable=false)
     */
    private $idDay = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_date", type="integer", nullable=false)
     */
    private $idDate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="classroom", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $classroom = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_begin", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $dateBegin = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $dateEnd = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pause_begin", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $pauseBegin = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pause_end", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $pauseEnd = null;

    /**
     * @var string
     *
     * @ORM\Column(name="calendarId", type="string", length=255, nullable=false)
     */
    private $calendarid;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="deleted", type="boolean", nullable=true)
     */
    private $deleted = '0';


}
