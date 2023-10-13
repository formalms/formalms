<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningScormItems
 *
 * @ORM\Table(name="learning_scorm_items", uniqueConstraints={@ORM\UniqueConstraint(name="idscorm_organization", columns={"idscorm_organization", "item_identifier"})})
 * @ORM\Entity
 */
class LearningScormItems
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_item", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idscormItem;

    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_organization", type="integer", nullable=false)
     */
    private $idscormOrganization = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="idscorm_parentitem", type="integer", nullable=true)
     */
    private $idscormParentitem;

    /**
     * @var string|null
     *
     * @ORM\Column(name="adlcp_prerequisites", type="string", length=200, nullable=true)
     */
    private $adlcpPrerequisites;

    /**
     * @var string|null
     *
     * @ORM\Column(name="adlcp_maxtimeallowed", type="string", length=24, nullable=true)
     */
    private $adlcpMaxtimeallowed;

    /**
     * @var string|null
     *
     * @ORM\Column(name="adlcp_timelimitaction", type="string", length=24, nullable=true)
     */
    private $adlcpTimelimitaction;

    /**
     * @var string|null
     *
     * @ORM\Column(name="adlcp_datafromlms", type="string", length=255, nullable=true)
     */
    private $adlcpDatafromlms;

    /**
     * @var string|null
     *
     * @ORM\Column(name="adlcp_masteryscore", type="string", length=200, nullable=true)
     */
    private $adlcpMasteryscore;

    /**
     * @var string|null
     *
     * @ORM\Column(name="item_identifier", type="string", length=255, nullable=true)
     */
    private $itemIdentifier;

    /**
     * @var string|null
     *
     * @ORM\Column(name="identifierref", type="string", length=255, nullable=true)
     */
    private $identifierref;

    /**
     * @var int|null
     *
     * @ORM\Column(name="idscorm_resource", type="integer", nullable=true)
     */
    private $idscormResource;

    /**
     * @var array|null
     *
     * @ORM\Column(name="isvisible", type="simple_array", length=0, nullable=true, options={"default"="true"})
     */
    private $isvisible = 'true';

    /**
     * @var string|null
     *
     * @ORM\Column(name="parameters", type="string", length=100, nullable=true)
     */
    private $parameters;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title = '';

    /**
     * @var int
     *
     * @ORM\Column(name="nChild", type="integer", nullable=false)
     */
    private $nchild = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="nDescendant", type="integer", nullable=false)
     */
    private $ndescendant = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="adlcp_completionthreshold", type="string", length=10, nullable=false)
     */
    private $adlcpCompletionthreshold = '';


}
