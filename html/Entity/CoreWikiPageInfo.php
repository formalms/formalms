<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreWikiPageInfo
 *
 * @ORM\Table(name="core_wiki_page_info", indexes={
 *     @ORM\Index(name="page_id_idx", columns={"page_id"}),
 *     @ORM\Index(name="language_idx", columns={"language"})
 * })
 * @ORM\Entity
 */
class CoreWikiPageInfo
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
     * @var int
     *
     * @ORM\Column(name="page_id", type="integer", nullable=false)
     
     */
    private $pageId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=50, nullable=false)
     
     */
    private $language = '';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     */
    private $version = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $lastUpdate = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="wiki_id", type="integer", nullable=false)
     */
    private $wikiId = '0';


}
