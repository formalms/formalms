<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAggregatedCertCourse
 *
 * @ORM\Table(name="learning_aggregated_cert_course", indexes={@ORM\Index(name="idAssociation", columns={"idAssociation"})})
 * @ORM\Entity
 */
class LearningAggregatedCertCourse
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idAssociation", type="integer", nullable=false)
     */
    private $idassociation = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourseEdition", type="integer", nullable=false)
     */
    private $idcourseedition = '0';


}
