<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceRulesRoot
 *
 * @ORM\Table(name="conference_rules_root", indexes={
 *     @ORM\Index(name="system_type_idx", columns={"system_type"})
 * })
 * @ORM\Entity
 */
class ConferenceRulesRoot
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
     * @var string
     *
     * @ORM\Column(name="system_type", type="string", length=0, nullable=false, options={"default"="p2p"})
     
     */
    private $systemType = 'p2p';

    /**
     * @var string|null
     *
     * @ORM\Column(name="server_ip", type="string", length=255, nullable=true)
     */
    private $serverIp;

    /**
     * @var int|null
     *
     * @ORM\Column(name="server_port", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $serverPort;

    /**
     * @var string|null
     *
     * @ORM\Column(name="server_path", type="string", length=255, nullable=true)
     */
    private $serverPath;

    /**
     * @var int
     *
     * @ORM\Column(name="max_user_at_time", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $maxUserAtTime = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="max_room_at_time", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $maxRoomAtTime = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="max_subroom_for_room", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $maxSubroomForRoom = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_drawboard", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableDrawboard = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_livestream", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableLivestream = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_remote_desktop", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableRemoteDesktop = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_webcam", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableWebcam = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_audio", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableAudio = 'no';


}
