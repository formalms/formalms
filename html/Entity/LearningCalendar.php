<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCalendar
 *
 * @ORM\Table(name="learning_calendar")
 * @ORM\Entity
 */
class LearningCalendar
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=true)
     */
    private $idcourse;


}
