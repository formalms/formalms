<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningForumAccess
 *
 * @ORM\Table(name="learning_forum_access", indexes={
 *      @ORM\Index(name="id_forum_idx", columns={"idForum"}),
 *      @ORM\Index(name="id_member_idx", columns={"idMember"})
 * })
 * @ORM\Entity
 */
class LearningForumAccess
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
     * @ORM\Column(name="idForum", type="integer", nullable=false)
     
     */
    private $idforum = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idMember", type="integer", nullable=false)
     
     */
    private $idmember = '0';


}
