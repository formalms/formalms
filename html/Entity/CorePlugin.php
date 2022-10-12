<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorePlugin
 *
 * @ORM\Table(name="core_plugin", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @ORM\Entity
 */
class CorePlugin
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="plugin_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $pluginId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=64, nullable=false)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="category", type="string", length=255, nullable=true)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=16, nullable=false)
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=128, nullable=false)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=false)
     */
    private $link;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", nullable=false)
     */
    private $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="regroup", type="integer", nullable=false)
     */
    private $regroup;

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    private $active;

    /**
     * @var int
     *
     * @ORM\Column(name="core", type="integer", nullable=false)
     */
    private $core;


}
