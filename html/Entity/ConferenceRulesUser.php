<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceRulesUser
 *
 * @ORM\Table(name="conference_rules_user")
 * @ORM\Entity
 */
class ConferenceRulesUser
{
    use Timestamps;
    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUser;

    /**
     * @var int
     *
     * @ORM\Column(name="last_hit", type="integer", nullable=false)
     */
    private $lastHit = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_room", type="integer", nullable=false)
     */
    private $idRoom = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="userid", type="string", length=255, nullable=false)
     */
    private $userid = '';

    /**
     * @var string
     *
     * @ORM\Column(name="user_ip", type="string", length=15, nullable=false)
     */
    private $userIp = '';

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     */
    private $firstName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     */
    private $lastName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="auto_reload", type="boolean", nullable=false)
     */
    private $autoReload = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="banned_until", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $bannedUntil;

    /**
     * @var string
     *
     * @ORM\Column(name="chat_record", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $chatRecord = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="advice_insert", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $adviceInsert = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="write_in_chat", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $writeInChat = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="request_to_chat", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $requestToChat = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="create_public_subroom", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $createPublicSubroom = 'no';

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

    /**
     * @var string
     *
     * @ORM\Column(name="enable_drawboard_watch", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableDrawboardWatch = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_drawboard_draw", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableDrawboardDraw = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_livestream_watch", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableLivestreamWatch = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="enable_livestream_publish", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $enableLivestreamPublish = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="accept_private_message", type="string", length=0, nullable=false, options={"default"="no"})
     */
    private $acceptPrivateMessage = 'no';

    /**
     * @var string|null
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     */
    private $picture;


}
