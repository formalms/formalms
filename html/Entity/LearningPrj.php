<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPrj
 *
 * @ORM\Table(name="learning_prj")
 * @ORM\Entity
 */
class LearningPrj
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
     * @var string
     *
     * @ORM\Column(name="ptitle", type="string", length=255, nullable=false)
     */
    private $ptitle = '';

    /**
     * @var int
     *
     * @ORM\Column(name="pgroup", type="integer", nullable=false)
     */
    private $pgroup = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="pprog", type="boolean", nullable=false)
     */
    private $pprog = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="psfiles", type="boolean", nullable=false)
     */
    private $psfiles = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="pstasks", type="boolean", nullable=false)
     */
    private $pstasks = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="psnews", type="boolean", nullable=false)
     */
    private $psnews = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="pstodo", type="boolean", nullable=false)
     */
    private $pstodo = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="psmsg", type="boolean", nullable=false)
     */
    private $psmsg = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="cid", type="integer", nullable=false)
     */
    private $cid = '0';


}
