<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DashboardBlocks
 *
 * @ORM\Table(name="dashboard_blocks", uniqueConstraints={@ORM\UniqueConstraint(name="block_class_unique", columns={"block_class"})})
 * @ORM\Entity
 */
class DashboardBlocks
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
     * @var string
     *
     * @ORM\Column(name="block_class", type="string", length=255, nullable=false)
     */
    private $blockClass;

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
