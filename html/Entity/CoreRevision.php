<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRevision
 *
 * @ORM\Table(name="core_revision", indexes={
 *     @ORM\Index(name="type_idx", columns={"type"}),
 *     @ORM\Index(name="parent_id_idx", columns={"parent_id"}),
 *     @ORM\Index(name="version_idx", columns={"version"}),
 *     @ORM\Index(name="sub_key_idx", columns={"sub_key"})
 * })
 * @ORM\Entity
 */
class CoreRevision
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
     * @ORM\Column(name="type", type="string", length=0, nullable=false, options={"default"="faq"})
     
     */
    private $type = 'faq';

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     
     */
    private $parentId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     
     */
    private $version = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="sub_key", type="string", length=80, nullable=false)
     
     */
    private $subKey = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rev_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $revDate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=0, nullable=false)
     */
    private $content;


}
