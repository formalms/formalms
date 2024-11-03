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
     * @ORM\Column(name="when_do", type="datetime", nullable=true, options={"default"=NULL})
     
     */
    private $whenDo = null;

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
