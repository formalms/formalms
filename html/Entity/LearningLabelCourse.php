<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLabelCourse
 *
 * @ORM\Table(name="learning_label_course")
 * @ORM\Entity
 */
class LearningLabelCourse
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_common_label", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCommonLabel = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCourse = '0';


}
