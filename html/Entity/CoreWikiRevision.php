<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreWikiRevision
 *
 * @ORM\Table(name="core_wiki_revision", indexes={
 *     @ORM\Index(name="wiki_id_idx", columns={"wiki_id"}),
 *     @ORM\Index(name="page_id_idx", columns={"page_id"}),
 *     @ORM\Index(name="version_idx", columns={"version"}),
 *     @ORM\Index(name="language_idx", columns={"language"})
 * })
 * @ORM\Entity
 */
class CoreWikiRevision
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="wiki_id", type="integer", nullable=false)
     
     */
    private $wikiId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="page_id", type="integer", nullable=false)
     
     */
    private $pageId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     
     */
    private $version = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=50, nullable=false)
     
     */
    private $language = '0';

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
