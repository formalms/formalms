<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreAdminCourse
 *
 * @ORM\Table(name="core_admin_course")
 * @ORM\Entity
 */
class CoreAdminCourse
{
    /**
     * @var int
     *
     * @ORM\Column(name="idst_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type_of_entry", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $typeOfEntry = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_entry", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEntry = '0';


}
