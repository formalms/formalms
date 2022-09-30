<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceRulesAdmin
 *
 * @ORM\Table(name="conference_rules_admin", indexes={
 *     @ORM\Index(name="server_status_idx", columns={"server_status"})
 * })
 * @ORM\Entity
 */
class ConferenceRulesAdmin
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
     * @var string
     *
     * @ORM\Column(name="server_status", type="string", length=0, nullable=false, options={"default"="yes"})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $serverStatus = 'yes';

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


}
