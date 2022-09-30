<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningKbRel
 *
 * @ORM\Table(name="learning_kb_rel")
 * @ORM\Entity
 */
class LearningKbRel
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="res_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $resId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="parent_id", type="string", length=45, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $parentId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="rel_type", type="string", length=0, nullable=false, options={"default"="tag"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $relType = 'tag';


}
