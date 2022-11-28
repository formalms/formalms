<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCourseDatePresence
 *
 * @ORM\Table(name="learning_course_date_presence")
 * @ORM\Entity
 */
class LearningCourseDatePresence
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="day", type="date", nullable=false, options={"default"="0000-00-00"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $day = '0000-00-00';

    /**
     * @var int
     *
     * @ORM\Column(name="id_date", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idDate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_day", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idDay = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="presence", type="boolean", nullable=false)
     */
    private $presence = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="score", type="string", length=255, nullable=true)
     */
    private $score;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", length=65535, nullable=false)
     */
    private $note;


}
