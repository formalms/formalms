<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceRulesRoom
 *
 * @ORM\Table(name="conference_rules_room")
 * @ORM\Entity
 */
class ConferenceRulesRoom
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id_room", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idRoom;

    /**
     * @var string
     *
     * @ORM\Column(name="enable_recording_function", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableRecordingFunction = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_advice_insert", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableAdviceInsert = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_write", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableWrite = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_chat_recording", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableChatRecording = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_private_subroom", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enablePrivateSubroom = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_public_subroom", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enablePublicSubroom = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_drawboard_watch", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableDrawboardWatch = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_drawboard_write", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableDrawboardWrite = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_audio", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableAudio = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_webcam", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableWebcam = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_stream_watch", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableStreamWatch = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_strem_write", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableStremWrite = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_remote_desktop", type="string", length=0, nullable=false, options={"default"="noone"})
     */
    private $enableRemoteDesktop = 'noone';

    /**
     * @var string
     *
     * @ORM\Column(name="room_name", type="string", length=255, nullable=false)
     */
    private $roomName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="room_type", type="string", length=0, nullable=false, options={"default"="course"})
     */
    private $roomType = 'course';

    /**
     * @var int
     *
     * @ORM\Column(name="id_source", type="integer", nullable=false)
     */
    private $idSource = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="room_parent", type="integer", nullable=false)
     */
    private $roomParent = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="advice_one", type="text", length=65535, nullable=true)
     */
    private $adviceOne;

    /**
     * @var string|null
     *
     * @ORM\Column(name="advice_two", type="text", length=65535, nullable=true)
     */
    private $adviceTwo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="advice_three", type="text", length=65535, nullable=true)
     */
    private $adviceThree;

    /**
     * @var string|null
     *
     * @ORM\Column(name="room_logo", type="string", length=255, nullable=true)
     */
    private $roomLogo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="room_sponsor", type="string", length=255, nullable=true)
     */
    private $roomSponsor;


}
