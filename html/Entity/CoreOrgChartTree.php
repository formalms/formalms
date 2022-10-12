<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreOrgChartTree
 *
 * @ORM\Table(name="core_org_chart_tree")
 * @ORM\Entity
 */
class CoreOrgChartTree
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idOrg", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idorg;

    /**
     * @var int
     *
     * @ORM\Column(name="idParent", type="integer", nullable=false)
     */
    private $idparent = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text", length=65535, nullable=false)
     */
    private $path;

    /**
     * @var int
     *
     * @ORM\Column(name="lev", type="integer", nullable=false)
     */
    private $lev = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="iLeft", type="integer", nullable=false)
     */
    private $ileft = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="iRight", type="integer", nullable=false)
     */
    private $iright = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idst_oc", type="integer", nullable=false)
     */
    private $idstOc = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst_ocd", type="integer", nullable=false)
     */
    private $idstOcd = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="associated_policy", type="integer", nullable=true, options={"unsigned"=true})
     */
    private $associatedPolicy;

    /**
     * @var string|null
     *
     * @ORM\Column(name="associated_template", type="string", length=255, nullable=true)
     */
    private $associatedTemplate;


}
