<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreUserLogAttempt
 *
 * @ORM\Table(name="core_user_log_attempt")
 * @ORM\Entity
 */
class CoreUserLogAttempt
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="userid", type="string", length=255, nullable=false)
     */
    private $userid = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="attempt_at", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $attemptAt = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="attempt_number", type="integer", nullable=false)
     */
    private $attemptNumber = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="user_ip", type="string", length=255, nullable=false)
     */
    private $userIp = '';


}
