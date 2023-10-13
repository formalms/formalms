<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTest
 *
 * @ORM\Table(name="learning_test")
 * @ORM\Entity
 */
class LearningTest
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idTest", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idtest;

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="point_type", type="boolean", nullable=false)
     */
    private $pointType = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="point_required", type="float", precision=10, scale=0, nullable=false)
     */
    private $pointRequired = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="display_type", type="boolean", nullable=false)
     */
    private $displayType = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="order_type", type="boolean", nullable=false)
     */
    private $orderType = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="shuffle_answer", type="boolean", nullable=false)
     */
    private $shuffleAnswer = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="question_random_number", type="integer", nullable=false)
     */
    private $questionRandomNumber = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="save_keep", type="boolean", nullable=false)
     */
    private $saveKeep = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="mod_doanswer", type="boolean", nullable=false, options={"default"="1"})
     */
    private $modDoanswer = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_travel", type="boolean", nullable=false, options={"default"="1"})
     */
    private $canTravel = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_only_status", type="boolean", nullable=false)
     */
    private $showOnlyStatus = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_score", type="boolean", nullable=false, options={"default"="1"})
     */
    private $showScore = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_score_cat", type="boolean", nullable=false)
     */
    private $showScoreCat = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_doanswer", type="boolean", nullable=false)
     */
    private $showDoanswer = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_solution", type="boolean", nullable=false)
     */
    private $showSolution = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="time_dependent", type="boolean", nullable=false)
     */
    private $timeDependent = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="time_assigned", type="integer", nullable=false)
     */
    private $timeAssigned = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="penality_test", type="boolean", nullable=false)
     */
    private $penalityTest = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="penality_time_test", type="float", precision=10, scale=0, nullable=false)
     */
    private $penalityTimeTest = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="penality_quest", type="boolean", nullable=false)
     */
    private $penalityQuest = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="penality_time_quest", type="float", precision=10, scale=0, nullable=false)
     */
    private $penalityTimeQuest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="max_attempt", type="integer", nullable=false)
     */
    private $maxAttempt = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="hide_info", type="boolean", nullable=false)
     */
    private $hideInfo = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="order_info", type="string", length=65536, nullable=false)
     */
    private $orderInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="cf_info", type="string", length=65536, nullable=false)
     */
    private $cfInfo;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_suspension", type="boolean", nullable=false)
     */
    private $useSuspension = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="suspension_num_attempts", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $suspensionNumAttempts = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="suspension_num_hours", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $suspensionNumHours = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="suspension_prerequisites", type="boolean", nullable=false)
     */
    private $suspensionPrerequisites = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="chart_options", type="string", length=65536, nullable=false)
     */
    private $chartOptions;

    /**
     * @var bool
     *
     * @ORM\Column(name="mandatory_answer", type="boolean", nullable=false)
     */
    private $mandatoryAnswer = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="score_max", type="integer", nullable=false)
     */
    private $scoreMax = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="obj_type", type="string", length=45, nullable=true, options={"default"="test"})
     */
    private $objType = 'test';

    /**
     * @var bool
     *
     * @ORM\Column(name="retain_answers_history", type="boolean", nullable=false)
     */
    private $retainAnswersHistory = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="gradimento", type="integer", nullable=true)
     */
    private $gradimento;


}
