<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreDomainConfigs
 *
 * @ORM\Table(name="core_domain_configs")
 * @ORM\Entity
 */
class CoreDomainConfigs
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
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="domain", type="string", length=255, nullable=true)
     */
    private $domain;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parentId", type="integer", nullable=true)
     */
    private $parentid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=true)
     */
    private $template;

    /**
     * @var int|null
     *
     * @ORM\Column(name="orgId", type="integer", nullable=true)
     */
    private $orgid;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mailConfigId", type="integer", nullable=true)
     */
    private $mailconfigid;


}
