<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DashboardLayouts
 *
 * @ORM\Table(name="dashboard_layouts", uniqueConstraints={@ORM\UniqueConstraint(name="name_idx", columns={"name"})}, indexes={@ORM\Index(name="status_idx", columns={"status"})})
 * @ORM\Entity
 */
class DashboardLayouts
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=255, nullable=false)
     */
    private $caption;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    private $status;

    /**
     * @var bool
     *
     * @ORM\Column(name="default", type="boolean", nullable=false)
     */
    private $default = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $createdAt = '0000-00-00 00:00:00';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;


}
