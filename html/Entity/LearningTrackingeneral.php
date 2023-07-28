<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTrackingeneral
 *
 * @ORM\Table(name="learning_trackingeneral")
 * @ORM\Entity
 */
class LearningTrackingeneral
{
    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idtrack;

    /**
     * @var int
     *
     * @ORM\Column(name="idEnter", type="integer", nullable=false)
     */
    private $identer = '0';

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
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=255, nullable=false)
     */
    private $sessionId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=250, nullable=false)
     */
    private $function = '';

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timeof", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $timeof = null;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=30, nullable=false)
     */
    private $ip = '';


}
