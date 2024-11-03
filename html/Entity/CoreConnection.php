<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreConnection
 *
 * @ORM\Table(name="core_connection", indexes={
 *     @ORM\Index(name="name_idx", columns={"name"})
 * })
 * @ORM\Entity
 */
class CoreConnection
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
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     
     */
    private $name = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=false)
     */
    private $type = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="params", type="string", length=65536, nullable=true, options={"default"=NULL})
     */
    private $params;


}
