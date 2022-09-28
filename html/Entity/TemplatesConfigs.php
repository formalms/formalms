<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TemplatesConfigs
 *
 * @ORM\Table(name="templates_configs", uniqueConstraints={@ORM\UniqueConstraint(name="template", columns={"template"})})
 * @ORM\Entity
 */
class TemplatesConfigs
{
    /**
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $template;

    /**
     * @var int|null
     *
     * @ORM\Column(name="idCatalogue", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $idcatalogue;

    /**
     * @var string|null
     *
     * @ORM\Column(name="colors", type="text", length=65535, nullable=true)
     */
    private $colors;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true, options={"default"="0000-00-00 00:00:00"})
     */
    private $createdAt = '0000-00-00 00:00:00';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;


}
