<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningKbTree
 *
 * @ORM\Table(name="learning_kb_tree")
 * @ORM\Entity
 */
class LearningKbTree
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="node_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $nodeId;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    private $parentId = '0';

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


}
