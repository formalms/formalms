<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * LearningCourseDatePresence
 *
 * @ORM\Table(name="learning_course_date_presence", indexes={
 *     @ORM\Index(name="id_date_idx", columns={"id_date"}),
 *     @ORM\Index(name="day_idx", columns={"day"}),
 *      @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningCourseDatePresence
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="day", type="date", nullable=true, options={"default"=NULL})
     */
    private $day = null;

    /**
     * @var int
     *
     * @ORM\Column(name="id_date", type="integer", nullable=false, options={"unsigned"=true})

     */
    private $idDate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false, options={"unsigned"=true})

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
     * @ORM\Column(name="note", type="string", length=65536, nullable=false)
     */
    private $note;


}
