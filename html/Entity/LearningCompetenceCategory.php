<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetenceCategory
 *
 * @ORM\Table(name="learning_competence_category")
 * @ORM\Entity
 */
class LearningCompetenceCategory
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id_category", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCategory;

    /**
     * @var int
     *
     * @ORM\Column(name="id_parent", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idParent = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $level = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="iLeft", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $ileft = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="iRight", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $iright = '0';


}
