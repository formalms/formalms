<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReservationEvents
 *
 * @ORM\Table(name="learning_reservation_events")
 * @ORM\Entity
 */
class LearningReservationEvents
{
    /**
     * @var int
     *
     * @ORM\Column(name="idEvent", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idevent;

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idLaboratory", type="integer", nullable=false)
     */
    private $idlaboratory = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     */
    private $idcategory = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=0, nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false, options={"default"="0000-00-00"})
     */
    private $date = '0000-00-00';

    /**
     * @var int
     *
     * @ORM\Column(name="maxUser", type="integer", nullable=false)
     */
    private $maxuser = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadLine", type="date", nullable=false, options={"default"="0000-00-00"})
     */
    private $deadline = '0000-00-00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fromTime", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $fromtime = '00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="toTime", type="time", nullable=false, options={"default"="00:00:00"})
     */
    private $totime = '00:00:00';


}
