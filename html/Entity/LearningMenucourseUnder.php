<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningMenucourseUnder
 *
 * @ORM\Table(name="learning_menucourse_under", indexes={
 *      @ORM\Index(name="id_course_idx", columns={"idCourse"}),
 *      @ORM\Index(name="id_module_idx", columns={"idModule"})
 * })
 * @ORM\Entity
 */
class LearningMenucourseUnder
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
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idModule", type="integer", nullable=false)
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
