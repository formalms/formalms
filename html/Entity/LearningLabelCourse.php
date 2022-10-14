<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLabelCourse
 *
 * @ORM\Table(name="learning_label_course", indexes={
 *      @ORM\Index(name="id_common_label_idx", columns={"id_common_label"}),
 *      @ORM\Index(name="id_course_idx", columns={"id_course"})
 * })
 * @ORM\Entity
 */
class LearningLabelCourse
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
     * @ORM\Column(name="id_common_label", type="integer", nullable=false)
     
     */
    private $idCommonLabel = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     
     */
    private $idCourse = '0';


}
