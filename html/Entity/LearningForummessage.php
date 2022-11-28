<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningForummessage
 *
 * @ORM\Table(name="learning_forummessage")
 * @ORM\Entity
 */
class LearningForummessage
{
    /**
     * @var int
     *
     * @ORM\Column(name="idMessage", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmessage;

    /**
     * @var int
     *
     * @ORM\Column(name="idThread", type="integer", nullable=false)
     */
    private $idthread = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="answer_tree", type="text", length=65535, nullable=false)
     */
    private $answerTree;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="textof", type="text", length=65535, nullable=false)
     */
    private $textof;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="posted", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $posted = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="generator", type="boolean", nullable=false)
     */
    private $generator = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="attach", type="string", length=255, nullable=false)
     */
    private $attach = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="locked", type="boolean", nullable=false)
     */
    private $locked = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="modified_by", type="integer", nullable=false)
     */
    private $modifiedBy = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified_by_on", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $modifiedByOn = '0000-00-00 00:00:00';


}
