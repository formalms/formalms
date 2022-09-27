<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCodeCourse
 *
 * @ORM\Table(name="core_code_course")
 * @ORM\Entity
 */
class CoreCodeCourse
{
    /**
     * @var int
     *
     * @ORM\Column(name="idCodeGroup", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcodegroup = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcourse = '0';


}
