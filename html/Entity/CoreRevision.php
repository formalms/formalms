<?php



namespace Formalms\Entity;

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
     * @ORM\Column(name="type", type="string", length=0, nullable=false, options={"default"="faq"})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $type = 'faq';

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $parentId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $version = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="sub_key", type="string", length=80, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
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
     * @ORM\Column(name="rev_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $revDate = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=0, nullable=false)
     */
    private $content;


}
