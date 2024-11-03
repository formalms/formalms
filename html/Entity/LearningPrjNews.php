<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPrjNews
 *
 * @ORM\Table(name="learning_prj_news")
 * @ORM\Entity
 */
class LearningPrjNews
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="pid", type="integer", nullable=false)
     */
    private $pid = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="ntitle", type="string", length=255, nullable=false)
     */
    private $ntitle = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ndate", type="date", nullable=true, options={"default"=NULL})
     */
    private $ndate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="ntxt", type="string", length=65536, nullable=false)
     */
    private $ntxt;


}
