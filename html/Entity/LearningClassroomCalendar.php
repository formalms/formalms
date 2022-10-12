<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningClassroomCalendar
 *
 * @ORM\Table(name="learning_classroom_calendar")
 * @ORM\Entity
 */
class LearningClassroomCalendar
{
    use Timestamps;    
      
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
     * @ORM\Column(name="classroom_id", type="integer", nullable=false)
     */
    private $classroomId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="owner", type="integer", nullable=false)
     */
    private $owner = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $startDate = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $endDate = '0000-00-00 00:00:00';


}
