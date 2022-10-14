<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningHtmlfront
 *
 * @ORM\Table(name="learning_htmlfront", indexes={
 *      @ORM\Index(name="id_course_idx", columns={"id_course"})
 * })
 * @ORM\Entity
 */
class LearningHtmlfront
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
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     
     */
    private $idCourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="textof", type="text", length=65535, nullable=false)
     */
    private $textof;


}
