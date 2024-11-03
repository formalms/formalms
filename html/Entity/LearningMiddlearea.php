<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningMiddlearea
 *
 * @ORM\Table(name="learning_middlearea", indexes={
 *      @ORM\Index(name="obj_index_idx", columns={"obj_index"})
 * })
 * @ORM\Entity
 */
class LearningMiddlearea
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_index", type="string", length=255, nullable=false)
     
     */
    private $objIndex = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="disabled", type="boolean", nullable=false)
     */
    private $disabled = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="idst_list", type="string", length=65536, nullable=false)
     */
    private $idstList;

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_home", type="boolean", nullable=false)
     */
    private $isHome = '0';


}
