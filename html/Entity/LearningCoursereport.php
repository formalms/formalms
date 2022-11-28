<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCoursereport
 *
 * @ORM\Table(name="learning_coursereport")
 * @ORM\Entity
 */
class LearningCoursereport
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_report", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idReport;

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     */
    private $idCourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var float
     *
     * @ORM\Column(name="max_score", type="float", precision=10, scale=0, nullable=false)
     */
    private $maxScore = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="required_score", type="float", precision=10, scale=0, nullable=false)
     */
    private $requiredScore = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="weight", type="integer", nullable=false)
     */
    private $weight = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="show_to_user", type="string", length=0, nullable=false, options={"default"="true"})
     */
    private $showToUser = 'true';

    /**
     * @var string
     *
     * @ORM\Column(name="use_for_final", type="string", length=0, nullable=false, options={"default"="true"})
     */
    private $useForFinal = 'true';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="source_of", type="string", length=0, nullable=false, options={"default"="test"})
     */
    private $sourceOf = 'test';

    /**
     * @var string
     *
     * @ORM\Column(name="id_source", type="string", length=255, nullable=false)
     */
    private $idSource = '0';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="show_in_detail", type="boolean", nullable=true, options={"default"="1"})
     */
    private $showInDetail = true;


}
