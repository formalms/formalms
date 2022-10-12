<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCoursepathUser
 *
 * @ORM\Table(name="learning_coursepath_user", indexes={
 *     @ORM\Index(name="id_path_idx", columns={"id_path"}),
 *     @ORM\Index(name="id_user_idx", columns={"idUser"})
 * })
 * @ORM\Entity
 */
class LearningCoursepathUser
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
     * @var int
     *
     * @ORM\Column(name="id_path", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idPath = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $iduser = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="waiting", type="boolean", nullable=false)
     */
    private $waiting = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="subscribed_by", type="integer", nullable=false)
     */
    private $subscribedBy = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="course_completed", type="integer", nullable=false)
     */
    private $courseCompleted = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_assign", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateAssign = '0000-00-00 00:00:00';


}
