<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningKbTag
 *
 * @ORM\Table(name="learning_kb_tag")
 * @ORM\Entity
 */
class LearningKbTag
{
    /**
     * @var int
     *
     * @ORM\Column(name="tag_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $tagId;

    /**
     * @var string
     *
     * @ORM\Column(name="tag_name", type="string", length=255, nullable=false)
     */
    private $tagName = '';


}
