<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreTransactionInfo
 *
 * @ORM\Table(name="core_transaction_info")
 * @ORM\Entity
 */
class CoreTransactionInfo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_trans", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idTrans = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_date", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idDate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_edition", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEdition = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code = '';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", length=255, nullable=false)
     */
    private $price = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="activated", type="boolean", nullable=false)
     */
    private $activated = '0';


}
