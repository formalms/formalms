<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreDeletedUser
 *
 * @ORM\Table(name="core_deleted_user")
 * @ORM\Entity
 */
class CoreDeletedUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_deletion", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDeletion;

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
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
     * @ORM\Column(name="pass", type="string", length=50, nullable=false)
     */
    private $pass = '';

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email = '';

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=false)
     */
    private $photo = '';

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
     * @var \DateTime
     *
     * @ORM\Column(name="lastenter", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $lastenter = '0000-00-00 00:00:00';

    /**
     * @var bool
     *
     * @ORM\Column(name="valid", type="boolean", nullable=false)
     */
    private $valid = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pwd_expire_at", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $pwdExpireAt = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="register_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $registerDate = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deletion_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $deletionDate = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="deleted_by", type="integer", nullable=false)
     */
    private $deletedBy = '0';


}
