<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCourseDate
 *
 * @ORM\Table(name="learning_course_date", indexes={@ORM\Index(name="id_course", columns={"id_course"})})
 * @ORM\Entity
 */
class LearningCourseDate
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_date", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDate;

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idCourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code = '';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="max_par", type="integer", nullable=false)
     */
    private $maxPar = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", length=255, nullable=false)
     */
    private $price = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="overbooking", type="boolean", nullable=false)
     */
    private $overbooking = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="test_type", type="boolean", nullable=false)
     */
    private $testType = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $status = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="medium_time", type="integer", nullable=false)
     */
    private $mediumTime = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sub_start_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $subStartDate = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sub_end_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $subEndDate = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="unsubscribe_date_limit", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $unsubscribeDateLimit = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="calendarId", type="string", length=255, nullable=false)
     */
    private $calendarid;


}
