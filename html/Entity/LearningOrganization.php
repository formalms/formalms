<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningOrganization
 *
 * @ORM\Table(name="learning_organization", indexes={
 *      @ORM\Index(name="path", columns={"path"}), 
 *      @ORM\Index(name="idParent", columns={"idParent"}),
 *      @ORM\Index(name="objectType_idResourse_idx", columns={"objectType","idResource"})
 * })
 * @ORM\Entity
 */
class LearningOrganization
{
    /**
     * @var int
     *
     * @ORM\Column(name="idOrg", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idorg;

    /**
     * @var int
     *
     * @ORM\Column(name="idParent", type="integer", nullable=false)
     */
    private $idparent = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=false)
     */
    private $path = '';

    /**
     * @var int
     *
     * @ORM\Column(name="lev", type="integer", nullable=false)
     */
    private $lev = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="objectType", type="string", length=20, nullable=false)
     */
    private $objecttype = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idResource", type="integer", nullable=false)
     */
    private $idresource = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     */
    private $idcategory = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idAuthor", type="integer", nullable=false)
     */
    private $idauthor = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=8, nullable=false)
     */
    private $version = '';

    /**
     * @var string
     *
     * @ORM\Column(name="difficult", type="string", length=0, nullable=false, options={"default"="_VERYEASY"})
     */
    private $difficult = '_VERYEASY';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=50, nullable=false)
     */
    private $language = '';

    /**
     * @var string
     *
     * @ORM\Column(name="resource", type="string", length=255, nullable=false)
     */
    private $resource = '';

    /**
     * @var string
     *
     * @ORM\Column(name="objective", type="text", length=65535, nullable=false)
     */
    private $objective;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateInsert", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $dateinsert = null;

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="prerequisites", type="string", length=255, nullable=false)
     */
    private $prerequisites = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="isTerminator", type="boolean", nullable=false)
     */
    private $isterminator = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idParam", type="integer", nullable=false)
     */
    private $idparam = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false, options={"default"="1"})
     */
    private $visible = true;

    /**
     * @var string
     *
     * @ORM\Column(name="milestone", type="string", length=0, nullable=false, options={"default"="-"})
     */
    private $milestone = '-';

    /**
     * @var string
     *
     * @ORM\Column(name="width", type="string", length=4, nullable=false)
     */
    private $width = '';

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="string", length=4, nullable=false)
     */
    private $height = '';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="publish_from", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $publishFrom;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="publish_to", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $publishTo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="access", type="string", length=255, nullable=true)
     */
    private $access;

    /**
     * @var int
     *
     * @ORM\Column(name="publish_for", type="integer", nullable=false)
     */
    private $publishFor = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="ignoreScore", type="boolean", nullable=false)
     */
    private $ignorescore = '0';


}
