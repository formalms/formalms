<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCategory
 *
 * @ORM\Table(name="learning_category")
 * @ORM\Entity
 */
class LearningCategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcategory;

    /**
     * @var int|null
     *
     * @ORM\Column(name="idParent", type="integer", nullable=true)
     */
    private $idparent = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="lev", type="integer", nullable=false)
     */
    private $lev = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text", length=65535, nullable=false)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

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


}
