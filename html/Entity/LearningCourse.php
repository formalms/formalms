<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCourse
 *
 * @ORM\Table(name="learning_course")
 * @ORM\Entity
 */
class LearningCourse
{
    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcourse;

    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     */
    private $idcategory = '0';

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
     * @ORM\Column(name="box_description", type="text", length=65535, nullable=false)
     */
    private $boxDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=100, nullable=false)
     */
    private $langCode = '';

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="level_show_user", type="integer", nullable=false)
     */
    private $levelShowUser = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="subscribe_method", type="boolean", nullable=false)
     */
    private $subscribeMethod = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="linkSponsor", type="string", length=255, nullable=false)
     */
    private $linksponsor = '';

    /**
     * @var string
     *
     * @ORM\Column(name="imgSponsor", type="string", length=255, nullable=false)
     */
    private $imgsponsor = '';

    /**
     * @var string
     *
     * @ORM\Column(name="img_course", type="string", length=255, nullable=false)
     */
    private $imgCourse = '';

    /**
     * @var string
     *
     * @ORM\Column(name="img_material", type="string", length=255, nullable=false)
     */
    private $imgMaterial = '';

    /**
     * @var string
     *
     * @ORM\Column(name="img_othermaterial", type="string", length=255, nullable=false)
     */
    private $imgOthermaterial = '';

    /**
     * @var string
     *
     * @ORM\Column(name="course_demo", type="string", length=255, nullable=false)
     */
    private $courseDemo = '';

    /**
     * @var int
     *
     * @ORM\Column(name="mediumTime", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $mediumtime = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="permCloseLO", type="boolean", nullable=false)
     */
    private $permcloselo = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="userStatusOp", type="integer", nullable=false)
     */
    private $userstatusop = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="difficult", type="string", length=0, nullable=false, options={"default"="medium"})
     */
    private $difficult = 'medium';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_progress", type="boolean", nullable=false, options={"default"="1"})
     */
    private $showProgress = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_time", type="boolean", nullable=false)
     */
    private $showTime = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_who_online", type="boolean", nullable=false)
     */
    private $showWhoOnline = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_extra_info", type="boolean", nullable=false)
     */
    private $showExtraInfo = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_rules", type="boolean", nullable=false)
     */
    private $showRules = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_begin", type="date", nullable=true, options={"default"="NULL"})
     */
    private $dateBegin = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="date", nullable=true, options={"default"="NULL"})
     */
    private $dateEnd = null;

    /**
     * @var string
     *
     * @ORM\Column(name="hour_begin", type="string", length=5, nullable=false)
     */
    private $hourBegin = '';

    /**
     * @var string
     *
     * @ORM\Column(name="hour_end", type="string", length=5, nullable=false)
     */
    private $hourEnd = '';

    /**
     * @var int
     *
     * @ORM\Column(name="valid_time", type="integer", nullable=false)
     */
    private $validTime = '0';

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
     * @var float
     *
     * @ORM\Column(name="max_sms_budget", type="float", precision=10, scale=0, nullable=false)
     */
    private $maxSmsBudget = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="selling", type="boolean", nullable=false)
     */
    private $selling = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="prize", type="string", length=255, nullable=false)
     */
    private $prize = '';

    /**
     * @var string
     *
     * @ORM\Column(name="course_type", type="string", length=255, nullable=false, options={"default"="elearning"})
     */
    private $courseType = 'elearning';

    /**
     * @var string
     *
     * @ORM\Column(name="policy_point", type="string", length=255, nullable=false)
     */
    private $policyPoint = '';

    /**
     * @var int
     *
     * @ORM\Column(name="point_to_all", type="integer", nullable=false)
     */
    private $pointToAll = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="course_edition", type="boolean", nullable=false)
     */
    private $courseEdition = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="classrooms", type="string", length=255, nullable=false)
     */
    private $classrooms = '';

    /**
     * @var string
     *
     * @ORM\Column(name="certificates", type="string", length=255, nullable=false)
     */
    private $certificates = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $createDate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="security_code", type="string", length=255, nullable=false)
     */
    private $securityCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="imported_from_connection", type="string", length=255, nullable=true)
     */
    private $importedFromConnection;

    /**
     * @var string
     *
     * @ORM\Column(name="course_quota", type="string", length=255, nullable=false, options={"default"="-1"})
     */
    private $courseQuota = '-1';

    /**
     * @var string
     *
     * @ORM\Column(name="used_space", type="string", length=255, nullable=false)
     */
    private $usedSpace = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="course_vote", type="float", precision=10, scale=0, nullable=false)
     */
    private $courseVote = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_overbooking", type="boolean", nullable=false)
     */
    private $allowOverbooking = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="can_subscribe", type="boolean", nullable=false)
     */
    private $canSubscribe = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="sub_start_date", type="datetime", nullable=true)
     */
    private $subStartDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="sub_end_date", type="datetime", nullable=true)
     */
    private $subEndDate;

    /**
     * @var string
     *
     * @ORM\Column(name="advance", type="string", length=255, nullable=false)
     */
    private $advance = '';

    /**
     * @var string
     *
     * @ORM\Column(name="autoregistration_code", type="string", length=255, nullable=false)
     */
    private $autoregistrationCode = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="direct_play", type="boolean", nullable=false)
     */
    private $directPlay = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="use_logo_in_courselist", type="boolean", nullable=false)
     */
    private $useLogoInCourselist = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_result", type="boolean", nullable=false)
     */
    private $showResult = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="credits", type="float", precision=10, scale=0, nullable=false)
     */
    private $credits = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="auto_unsubscribe", type="boolean", nullable=false)
     */
    private $autoUnsubscribe = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="unsubscribe_date_limit", type="datetime", nullable=true)
     */
    private $unsubscribeDateLimit;

    /**
     * @var int|null
     *
     * @ORM\Column(name="id_menucustom", type="integer", nullable=true)
     */
    private $idMenucustom;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="sendCalendar", type="boolean", nullable=true)
     */
    private $sendcalendar = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="calendarId", type="string", length=255, nullable=false)
     */
    private $calendarid;


}
