<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreUser
 *
 * @ORM\Table(name="core_user", uniqueConstraints={@ORM\UniqueConstraint(name="linkedin_id", columns={"linkedin_id"}), @ORM\UniqueConstraint(name="facebook_id", columns={"facebook_id"}), @ORM\UniqueConstraint(name="google_id", columns={"google_id"}), @ORM\UniqueConstraint(name="twitter_id", columns={"twitter_id"}), @ORM\UniqueConstraint(name="userid", columns={"userid"})})
 * @ORM\Entity
 */
class CoreUser
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="userid", type="string", length=255, nullable=false)
     */
    private $userid = '';

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=false)
     */
    private $firstname = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=false)
     */
    private $lastname = '';

    /**
     * @var string
     *
     * @ORM\Column(name="pass", type="string", length=255, nullable=false)
     */
    private $pass;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email = '';

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=false)
     */
    private $avatar = '';

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="text", length=65535, nullable=false)
     */
    private $signature;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="lastenter", type="datetime", nullable=true)
     */
    private $lastenter;

    /**
     * @var bool
     *
     * @ORM\Column(name="valid", type="boolean", nullable=false, options={"default"="1"})
     */
    private $valid = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pwd_expire_at", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $pwdExpireAt = '0000-00-00 00:00:00';

    /**
     * @var bool
     *
     * @ORM\Column(name="privacy_policy", type="boolean", nullable=false)
     */
    private $privacyPolicy = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="force_change", type="boolean", nullable=false)
     */
    private $forceChange = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="register_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $registerDate = '0000-00-00 00:00:00';

    /**
     * @var string|null
     *
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    private $facebookId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="twitter_id", type="string", length=255, nullable=true)
     */
    private $twitterId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="linkedin_id", type="string", length=255, nullable=true)
     */
    private $linkedinId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    private $googleId;


}
