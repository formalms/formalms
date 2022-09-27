<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreUserFile
 *
 * @ORM\Table(name="core_user_file")
 * @ORM\Entity
 */
class CoreUserFile
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
     * @var int
     *
     * @ORM\Column(name="user_idst", type="integer", nullable=false)
     */
    private $userIdst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=20, nullable=false)
     */
    private $type = '';

    /**
     * @var string
     *
     * @ORM\Column(name="fname", type="string", length=255, nullable=false)
     */
    private $fname = '';

    /**
     * @var string
     *
     * @ORM\Column(name="real_fname", type="string", length=255, nullable=false)
     */
    private $realFname = '';

    /**
     * @var string
     *
     * @ORM\Column(name="media_url", type="string", length=255, nullable=false)
     */
    private $mediaUrl = '';

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", nullable=false)
     */
    private $size = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="uldate", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $uldate = '0000-00-00 00:00:00';


}
