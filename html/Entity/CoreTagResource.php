<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreTagResource
 *
 * @ORM\Table(name="core_tag_resource")
 * @ORM\Entity
 */
class CoreTagResource
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_resource", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idResource = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="resource_type", type="string", length=255, nullable=false)
     * @ORM\Id
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
