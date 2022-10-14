<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceDimdim
 *
 * @ORM\Table(name="conference_dimdim", indexes={@ORM\Index(name="idConference", columns={"idConference"})})
 * @ORM\Entity
 */
class ConferenceDimdim
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
     * @var int
     *
     * @ORM\Column(name="idConference", type="bigint", nullable=false)
     */
    private $idconference = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="confkey", type="string", length=255, nullable=true)
     */
    private $confkey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emailuser", type="string", length=255, nullable=true)
     */
    private $emailuser;

    /**
     * @var string|null
     *
     * @ORM\Column(name="displayname", type="string", length=255, nullable=true)
     */
    private $displayname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="timezone", type="string", length=255, nullable=true)
     */
    private $timezone;

    /**
     * @var int|null
     *
     * @ORM\Column(name="audiovideosettings", type="integer", nullable=true)
     */
    private $audiovideosettings;

    /**
     * @var int|null
     *
     * @ORM\Column(name="maxmikes", type="integer", nullable=true)
     */
    private $maxmikes;

    /**
     * @var string
     *
     * @ORM\Column(name="schedule_info", type="text", length=65535, nullable=false)
     */
    private $scheduleInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="extra_conf", type="text", length=65535, nullable=false)
     */
    private $extraConf;


}
