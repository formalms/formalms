<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningWikiCourse
 *
 * @ORM\Table(name="learning_wiki_course", indexes={
 *      @ORM\Index(name="course_id_idx", columns={"course_id"}),
 *      @ORM\Index(name="wiki_id_idx", columns={"wiki_id"})
 * })
 * @ORM\Entity
 */
class LearningWikiCourse
{

    use Timestamps;

    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="course_id", type="integer", nullable=false)
     
     */
    private $courseId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="wiki_id", type="integer", nullable=false)
     
     */
    private $wikiId = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_owner", type="boolean", nullable=false)
     */
    private $isOwner = '0';


}
