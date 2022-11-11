<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreUserTemp
 *
 * @ORM\Table(name="core_user_temp", uniqueConstraints={@ORM\UniqueConstraint(name="userid", columns={"userid"})})
 * @ORM\Entity
 */
class CoreUserTemp
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
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstname = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=false)
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
     * @ORM\Column(name="language", type="string", length=50, nullable=false)
     */
    private $language = '';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="request_on", type="datetime", nullable=true, options={"default"="0000-00-00 00:00:00"})
     */
    private $requestOn = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="random_code", type="string", length=255, nullable=false)
     */
    private $randomCode = '';

    /**
     * @var int
     *
     * @ORM\Column(name="create_by_admin", type="integer", nullable=false)
     */
    private $createByAdmin = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="confirmed", type="boolean", nullable=false)
     */
    private $confirmed = '0';

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

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=false)
     */
    private $avatar;


}
