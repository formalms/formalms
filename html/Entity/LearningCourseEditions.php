<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCourseEditions
 *
 * @ORM\Table(name="learning_course_editions")
 * @ORM\Entity
 */
class LearningCourseEditions
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id_edition", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEdition;

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     */
    private $idCourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=50, nullable=false)
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
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_begin", type="date", nullable=true, options={"default"=NULL})
     */
    private $dateBegin = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $dateEnd = null;

    /**
     * @var int
     *
     * @ORM\Column(name="max_num_subscribe", type="integer", nullable=false)
     */
    private $maxNumSubscribe = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="min_num_subscribe", type="integer", nullable=false)
     */
    private $minNumSubscribe = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", length=255, nullable=false)
     */
    private $price = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="overbooking", type="boolean", nullable=false)
     */
    private $overbooking = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="can_subscribe", type="boolean", nullable=false)
     */
    private $canSubscribe = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sub_date_begin", type="date", nullable=true, options={"default"=NULL})
     */
    private $subDateBegin = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sub_date_end", type="date", nullable=true, options={"default"=NULL})
     */
    private $subDateEnd = null;

    /**
     * @var string
     *
     * @ORM\Column(name="calendarId", type="string", length=255, nullable=false)
     */
    private $calendarid;


}
