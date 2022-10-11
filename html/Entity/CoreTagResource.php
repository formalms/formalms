<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreTagResource
 *
 * @ORM\Table(name="core_tag_resource", indexes={
 *     @ORM\Index(name="id_resource_idx", columns={"id_resource"}),
 *     @ORM\Index(name="resource_type_idx", columns={"resource_type"})
 * })
 * @ORM\Entity
 */
class CoreTagResource
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_resource", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idResource = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="resource_type", type="string", length=255, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $resourceType = '';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="sample_text", type="text", length=65535, nullable=false)
     */
    private $sampleText;

    /**
     * @var string
     *
     * @ORM\Column(name="permalink", type="text", length=65535, nullable=false)
     */
    private $permalink;


}
