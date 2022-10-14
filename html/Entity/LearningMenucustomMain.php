<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningMenucustomMain
 *
 * @ORM\Table(name="learning_menucustom_main")
 * @ORM\Entity
 */
class LearningMenucustomMain
{
    /**
     * @var int
     *
     * @ORM\Column(name="idMain", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmain;

    /**
     * @var int
     *
     * @ORM\Column(name="idCustom", type="integer", nullable=false)
     */
    private $idcustom = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=false)
     */
    private $image = '';


}
