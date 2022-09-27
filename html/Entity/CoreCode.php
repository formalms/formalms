<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCode
 *
 * @ORM\Table(name="core_code")
 * @ORM\Entity
 */
class CoreCode
{
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $code = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idCodeGroup", type="integer", nullable=false)
     */
    private $idcodegroup = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="used", type="boolean", nullable=false)
     */
    private $used = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="idUser", type="integer", nullable=true)
     */
    private $iduser;

    /**
     * @var bool
     *
     * @ORM\Column(name="unlimitedUse", type="boolean", nullable=false)
     */
    private $unlimiteduse = '0';


}
