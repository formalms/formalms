<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAssessmentRule
 *
 * @ORM\Table(name="learning_assessment_rule")
 * @ORM\Entity
 */
class LearningAssessmentRule
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="rule_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $ruleId;

    /**
     * @var int
     *
     * @ORM\Column(name="test_id", type="integer", nullable=false)
     */
    private $testId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer", nullable=false)
     */
    private $categoryId = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="from_score", type="float", precision=10, scale=0, nullable=false)
     */
    private $fromScore = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="to_score", type="float", precision=10, scale=0, nullable=false)
     */
    private $toScore = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="competences_list", type="text", length=65535, nullable=true)
     */
    private $competencesList;

    /**
     * @var string|null
     *
     * @ORM\Column(name="courses_list", type="text", length=65535, nullable=true)
     */
    private $coursesList;

    /**
     * @var string|null
     *
     * @ORM\Column(name="feedback_txt", type="text", length=65535, nullable=true)
     */
    private $feedbackTxt;


}
