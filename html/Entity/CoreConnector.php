<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreConnector
 *
 * @ORM\Table(name="core_connector", indexes={
 *     @ORM\Index(name="type_idx", columns={"type"})
 * })
 * @ORM\Entity
 */
class CoreConnector
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=25, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $type = '';

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255, nullable=false)
     */
    private $file = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=50, nullable=false)
     */
    private $class = '';


}
