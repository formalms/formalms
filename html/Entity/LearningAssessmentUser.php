<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAssessmentUser
 *
 * @ORM\Table(name="learning_assessment_user", indexes={
 *     @ORM\Index(name="id_assessment_idx", columns={"id_assessment"}),
 *     @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningAssessmentUser
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_assessment", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idAssessment = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type_of", type="string", length=255, nullable=false)
     */
    private $typeOf = '';


}
