<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCommentAjax
 *
 * @ORM\Table(name="learning_comment_ajax")
 * @ORM\Entity
 */
class LearningCommentAjax
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_comment", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idComment;

    /**
     * @var string
     *
     * @ORM\Column(name="resource_type", type="string", length=50, nullable=false)
     */
    private $resourceType = '';

    /**
     * @var string
     *
     * @ORM\Column(name="external_key", type="string", length=200, nullable=false)
     */
    private $externalKey = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_author", type="integer", nullable=false)
     */
    private $idAuthor = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="posted_on", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $postedOn = null;

    /**
     * @var string
     *
     * @ORM\Column(name="textof", type="text", length=65535, nullable=false)
     */
    private $textof;

    /**
     * @var string
     *
     * @ORM\Column(name="history_tree", type="string", length=255, nullable=false)
     */
    private $historyTree = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_parent", type="integer", nullable=false)
     */
    private $idParent = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="moderated", type="boolean", nullable=false)
     */
    private $moderated = '0';


}
