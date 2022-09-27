<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreMailConfigs
 *
 * @ORM\Table(name="core_mail_configs")
 * @ORM\Entity
 */
class CoreMailConfigs
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
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="system", type="boolean", nullable=false)
     */
    private $system = '0';


}
