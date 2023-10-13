<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DashboardBlockConfig
 *
 * @ORM\Table(name="dashboard_block_config", indexes={@ORM\Index(name="block_class_idx", columns={"block_class"}), @ORM\Index(name="dashboard_id_idx", columns={"dashboard_id"})})
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
     * @ORM\Column(name="block_config", type="string", length=65536, nullable=false)
     */
    private $blockConfig;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="bigint", nullable=false, options={"default"="999"})
     */
    private $position = '999';


    /**
     * @var int
     *
     * @ORM\Column(name="dashboard_id", type="bigint", nullable=true, options={"default"=NULL})
     */
    private $dashboardId;


}
