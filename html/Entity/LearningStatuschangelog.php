<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningStatuschangelog
 *
 * @ORM\Table(name="learning_statuschangelog")
 * @ORM\Entity
 */
class LearningStatuschangelog
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="when_do", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $whenDo = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcourse = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="status_user", type="boolean", nullable=false)
     */
    private $statusUser = '0';


}
