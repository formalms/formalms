<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningStatuschangelog
 *
 * @ORM\Table(name="learning_statuschangelog", indexes={
 *      @ORM\Index(name="when_do_idx", columns={"when_do"}),
 *      @ORM\Index(name="id_user_idx", columns={"idUser"}),
 *      @ORM\Index(name="id_course_idx", columns={"idCourse"})
 * })
 * @ORM\Entity
 */
class LearningStatuschangelog
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
     * @var \DateTime
     *
     * @ORM\Column(name="when_do", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     
     */
    private $whenDo = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     
     */
    private $idcourse = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="status_user", type="boolean", nullable=false)
     */
    private $statusUser = '0';


}
