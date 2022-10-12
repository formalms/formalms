<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCourseDatePresence
 *
 * @ORM\Table(name="learning_course_date_presence", indexes={
 *      @ORM\Index(name="day_idx", columns={"day"}), 
 *      @ORM\Index(name="id_date_idx", columns={"id_date"}),
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
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="day", type="date", nullable=false, options={"default"="0000-00-00"})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $day = '0000-00-00';

    /**
     * @var int
     *
     * @ORM\Column(name="id_date", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idDate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false, options={"unsigned"=true})
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
