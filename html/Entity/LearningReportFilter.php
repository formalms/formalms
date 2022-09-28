<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReportFilter
 *
 * @ORM\Table(name="learning_report_filter")
 * @ORM\Entity
 */
class LearningReportFilter
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_filter", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFilter;

    /**
     * @var int
     *
     * @ORM\Column(name="id_report", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idReport = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $author = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $creationDate = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="filter_name", type="string", length=255, nullable=false)
     */
    private $filterName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="filter_data", type="text", length=65535, nullable=false)
     */
    private $filterData;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=false)
     */
    private $isPublic = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="views", type="integer", nullable=false)
     */
    private $views = '0';


}
