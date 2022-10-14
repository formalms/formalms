<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCoursepathCourses
 *
 * @ORM\Table(name="learning_coursepath_courses", indexes={
 *     @ORM\Index(name="id_path_idx", columns={"id_path"}),
 *     @ORM\Index(name="id_item_idx", columns={"id_item"}),
 *     @ORM\Index(name="in_slot_idx", columns={"in_slot"})
 * })
 * @ORM\Entity
 */
class LearningCoursepathCourses
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
     * @ORM\Column(name="id_path", type="integer", nullable=false)
     
     */
    private $idPath = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_item", type="integer", nullable=false)
     
     */
    private $idItem = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="in_slot", type="integer", nullable=false)
     
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
