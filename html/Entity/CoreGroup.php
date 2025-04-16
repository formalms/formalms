<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreGroup
 *
 * @ORM\Table(name="core_group",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="groupid", columns={"groupid"})
 *      },
 *      indexes={
 *          @ORM\Index(name="core_group_hidden_type_idx", columns={"hidden","type"}),
 *          @ORM\Index(name="core_group_idst_idx", columns={"idst"}),
 *          @ORM\Index(name="core_group_groupid_idx", columns={"groupid"})
 *      }
 * )
 * @ORM\Entity
 */
class CoreGroup
{

    use Timestamps;
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
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
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
     * @ORM\Column(name="show_on_platform", type="string", length=65536, nullable=false)
     */
    private $showOnPlatform;


}
