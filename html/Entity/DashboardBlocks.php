<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DashboardBlocks
 *
 * @ORM\Table(name="dashboard_blocks", uniqueConstraints={@ORM\UniqueConstraint(name="block_class_unique", columns={"block_class"})})
 * @ORM\Entity
 */
class DashboardBlocks
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



}
