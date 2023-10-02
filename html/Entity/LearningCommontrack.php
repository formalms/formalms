<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCommontrack
 *
 * @ORM\Table(name="learning_commontrack", indexes={
 *      @ORM\Index(name="idUser", columns={"idUser"}), 
 *      @ORM\Index(name="idReference", columns={"idReference"})
 * })
 * @ORM\Entity
 */
class LearningCommontrack
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
     * @ORM\Column(name="idReference", type="integer", nullable=false)
     */
    private $idreference = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idtrack = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="objectType", type="string", length=20, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $objecttype = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="firstAttempt", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $firstattempt = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="first_complete", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $firstComplete;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_complete", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $lastComplete;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAttempt", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $dateattempt = null;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     */
    private $status = '';


}
