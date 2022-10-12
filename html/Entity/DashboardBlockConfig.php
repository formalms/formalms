<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DashboardBlockConfig
 *
 * @ORM\Table(name="dashboard_block_config", indexes={@ORM\Index(name="block_class_idx", columns={"block_class"}), @ORM\Index(name="config_layout_fk", columns={"dashboard_id"})})
 * @ORM\Entity
 */
class DashboardBlockConfig
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
     * @ORM\Column(name="block_class", type="string", length=255, nullable=false)
     */
    private $blockClass;

    /**
     * @var string
     *
     * @ORM\Column(name="block_config", type="text", length=65535, nullable=false)
     */
    private $blockConfig;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="bigint", nullable=false, options={"default"="999"})
     */
    private $position = '999';

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

    /**
     * @var \DashboardLayouts
     *
     * @ORM\ManyToOne(targetEntity="DashboardLayouts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dashboard_id", referencedColumnName="id")
     * })
     */
    private $dashboard;


}
