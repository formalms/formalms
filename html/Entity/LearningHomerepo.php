<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningHomerepo
 *
 * @ORM\Table(name="learning_homerepo", indexes={@ORM\Index(name="idParent", columns={"idParent"}), @ORM\Index(name="path", columns={"path"}), @ORM\Index(name="idOwner", columns={"idOwner"})})
 * @ORM\Entity
 */
class LearningHomerepo
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idRepo", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idrepo;

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
     * @ORM\Column(name="dateInsert", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateinsert = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="idOwner", type="integer", nullable=false)
     */
    private $idowner = '0';


}
