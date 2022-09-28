<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningMiddlearea
 *
 * @ORM\Table(name="learning_middlearea")
 * @ORM\Entity
 */
class LearningMiddlearea
{
    /**
     * @var string
     *
     * @ORM\Column(name="obj_index", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
     * @ORM\Column(name="idst_list", type="text", length=65535, nullable=false)
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
