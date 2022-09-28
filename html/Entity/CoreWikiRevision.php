<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreWikiRevision
 *
 * @ORM\Table(name="core_wiki_revision")
 * @ORM\Entity
 */
class CoreWikiRevision
{
    /**
     * @var int
     *
     * @ORM\Column(name="wiki_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $wikiId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="page_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $pageId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $version = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
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
