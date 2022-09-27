<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningMenucustomUnder
 *
 * @ORM\Table(name="learning_menucustom_under")
 * @ORM\Entity
 */
class LearningMenucustomUnder
{
    /**
     * @var int
     *
     * @ORM\Column(name="idCustom", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcustom = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idModule", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idmodule = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idMain", type="integer", nullable=false)
     */
    private $idmain = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="my_name", type="string", length=255, nullable=false)
     */
    private $myName = '';


}
