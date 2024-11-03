<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningKbTreeInfo
 *
 * @ORM\Table(name="learning_kb_tree_info")
 * @ORM\Entity
 */
class LearningKbTreeInfo
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
     * @ORM\Column(name="id_dir", type="integer", nullable=false)
     */
    private $idDir = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=50, nullable=false)
     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="node_title", type="string", length=255, nullable=false)
     */
    private $nodeTitle = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="node_desc", type="string", length=65536, nullable=true)
     */
    private $nodeDesc;


}
