<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreWiki
 *
 * @ORM\Table(name="core_wiki")
 * @ORM\Entity
 */
class CoreWiki
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="wiki_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $wikiId;

    /**
     * @var string
     *
     * @ORM\Column(name="source_platform", type="string", length=255, nullable=false)
     */
    private $sourcePlatform = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean", nullable=false)
     */
    private $public = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=50, nullable=false)
     */
    private $language = '';

    /**
     * @var string
     *
     * @ORM\Column(name="other_lang", type="text", length=65535, nullable=false)
     */
    private $otherLang;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $creationDate = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="page_count", type="integer", nullable=false)
     */
    private $pageCount = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="revision_count", type="integer", nullable=false)
     */
    private $revisionCount = '0';


}
