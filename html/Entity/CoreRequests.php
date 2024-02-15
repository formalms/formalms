<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRequests
 *
 * @ORM\Table(name="core_requests",
 *   indexes={
 *      @ORM\Index(name="app_name_idx", columns={"app","name"})
 *  }
 *  )
 * @ORM\Entity
 */
class CoreRequests
{

    use Timestamps;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="app", type="string", length=10, nullable=false)
     */
    private $app;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="controller", type="string", length=255, nullable=false)
     */
    private $controller;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="string", length=255, nullable=false)
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="plugin", type="string", length=255, nullable=false)
     */
    private $plugin;


}
