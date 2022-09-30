<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCoursepathCourses
 *
 * @ORM\Table(name="learning_coursepath_courses")
 * @ORM\Entity
 */
class LearningCoursepathCourses
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
     * @ORM\Column(name="id_path", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idPath = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_item", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idItem = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="in_slot", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $inSlot = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="prerequisites", type="text", length=65535, nullable=false)
     */
    private $prerequisites;

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';


}
