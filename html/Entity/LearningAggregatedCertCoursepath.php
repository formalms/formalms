<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAggregatedCertCoursepath
 *
 * @ORM\Table(name="learning_aggregated_cert_coursepath", indexes={@ORM\Index(name="idAssociation", columns={"idAssociation"})})
 * @ORM\Entity
 */
class LearningAggregatedCertCoursepath
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
     * @ORM\Column(name="idCoursePath", type="integer", nullable=false)
     */
    private $idcoursepath = '0';


}
