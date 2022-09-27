<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreWikiPage
 *
 * @ORM\Table(name="core_wiki_page")
 * @ORM\Entity
 */
class CoreWikiPage
{
    /**
     * @var int
     *
     * @ORM\Column(name="page_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $pageId;

    /**
     * @var string
     *
     * @ORM\Column(name="page_code", type="string", length=60, nullable=false)
     */
    private $pageCode;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    private $parentId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="page_path", type="string", length=255, nullable=false)
     */
    private $pagePath = '';

    /**
     * @var int
     *
     * @ORM\Column(name="lev", type="integer", nullable=false)
     */
    private $lev = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="wiki_id", type="integer", nullable=false)
     */
    private $wikiId = '0';


}
