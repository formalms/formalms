<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningKbRes
 *
 * @ORM\Table(name="learning_kb_res")
 * @ORM\Entity
 */
class LearningKbRes
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="res_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $resId;

    /**
     * @var string
     *
     * @ORM\Column(name="r_name", type="string", length=255, nullable=false)
     */
    private $rName = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="original_name", type="string", length=255, nullable=true)
     */
    private $originalName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="r_desc", type="text", length=65535, nullable=true)
     */
    private $rDesc;

    /**
     * @var int
     *
     * @ORM\Column(name="r_item_id", type="integer", nullable=false)
     */
    private $rItemId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="r_type", type="string", length=45, nullable=false)
     */
    private $rType = '';

    /**
     * @var string
     *
     * @ORM\Column(name="r_env", type="string", length=45, nullable=false)
     */
    private $rEnv = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="r_env_parent_id", type="integer", nullable=true)
     */
    private $rEnvParentId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="r_param", type="string", length=255, nullable=true)
     */
    private $rParam;

    /**
     * @var string|null
     *
     * @ORM\Column(name="r_alt_desc", type="string", length=255, nullable=true)
     */
    private $rAltDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="r_lang", type="string", length=50, nullable=false)
     */
    private $rLang = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="force_visible", type="boolean", nullable=false)
     */
    private $forceVisible = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_mobile", type="boolean", nullable=false)
     */
    private $isMobile = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="sub_categorize", type="boolean", nullable=false, options={"default"="-1"})
     */
    private $subCategorize = '-1';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_categorized", type="boolean", nullable=false)
     */
    private $isCategorized = '0';


}
