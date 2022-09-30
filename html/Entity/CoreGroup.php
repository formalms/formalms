<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreGroup
 *
 * @ORM\Table(name="core_group", uniqueConstraints={@ORM\UniqueConstraint(name="groupid", columns={"groupid"})})
 * @ORM\Entity
 */
class CoreGroup
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="groupid", type="string", length=255, nullable=false)
     */
    private $groupid = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="hidden", type="string", length=0, nullable=false, options={"default"="false"})
     */
    private $hidden = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=0, nullable=false, options={"default"="free"})
     */
    private $type = 'free';

    /**
     * @var string
     *
     * @ORM\Column(name="show_on_platform", type="text", length=65535, nullable=false)
     */
    private $showOnPlatform;


}
