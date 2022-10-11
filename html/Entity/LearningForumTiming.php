<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningForumTiming
 *
 * @ORM\Table(name="learning_forum_timing", indexes={
 *      @ORM\Index(name="id_user_idx", columns={"idUser"}),
 *      @ORM\Index(name="id_course_idx", columns={"idCourse"})
 * })
 * @ORM\Entity
 */
class LearningForumTiming
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcourse = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_access", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $lastAccess = '0000-00-00 00:00:00';


}
