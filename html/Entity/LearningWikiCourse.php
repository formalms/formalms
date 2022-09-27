<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningWikiCourse
 *
 * @ORM\Table(name="learning_wiki_course")
 * @ORM\Entity
 */
class LearningWikiCourse
{
    /**
     * @var int
     *
     * @ORM\Column(name="course_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $courseId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="wiki_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $wikiId = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_owner", type="boolean", nullable=false)
     */
    private $isOwner = '0';


}
