<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningKbRel
 *
 * @ORM\Table(name="learning_kb_rel", indexes={
 *      @ORM\Index(name="res_id_idx", columns={"res_id"}),
 *      @ORM\Index(name="parent_id_idx", columns={"parent_id"}),
 *      @ORM\Index(name="rel_type_idx", columns={"rel_type"})
 * })
 * @ORM\Entity
 */
class LearningKbRel
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="res_id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $resId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="parent_id", type="string", length=45, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $parentId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="rel_type", type="string", length=0, nullable=false, options={"default"="tag"})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $relType = 'tag';


}
