<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreAdminCourse
 *
 * @ORM\Table(name="core_admin_course", indexes={
 *     @ORM\Index(name="idst_user_idx", columns={"idst_user"}),
 *     @ORM\Index(name="type_of_entry_idx", columns={"type_of_entry"}),
 *     @ORM\Index(name="id_entry_idx", columns={"id_entry"})
 * })
 * @ORM\Entity
 */
class CoreAdminCourse
{
    use Timestamps;
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
     * @ORM\Column(name="idst_user", type="integer", nullable=false)
     
     */
    private $idstUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type_of_entry", type="string", length=50, nullable=false)
     
     */
    private $typeOfEntry = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_entry", type="integer", nullable=false)
     
     */
    private $idEntry = '0';


}
