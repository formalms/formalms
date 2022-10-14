<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreTransactionInfo
 *
 * @ORM\Table(name="core_transaction_info", indexes={
 *     @ORM\Index(name="id_trans_idx", columns={"id_trans"}),
 *     @ORM\Index(name="id_course_idx", columns={"id_course"}),
 *     @ORM\Index(name="id_date_idx", columns={"id_date"}),
 *     @ORM\Index(name="id_edition_idx", columns={"id_edition"})
 * })
 * @ORM\Entity
 */
class CoreTransactionInfo
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
     * @ORM\Column(name="id_trans", type="integer", nullable=false)
     
     */
    private $idTrans = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     
     */
    private $idCourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_date", type="integer", nullable=false)
     
     */
    private $idDate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_edition", type="integer", nullable=false)
     
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
