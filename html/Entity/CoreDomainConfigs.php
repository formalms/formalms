<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreDomainConfigs
 *
 * @ORM\Table(name="core_domain_configs")
 * @ORM\Entity
 */
class CoreDomainConfigs
{
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

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $updatedAt = 'CURRENT_TIMESTAMP';


}
