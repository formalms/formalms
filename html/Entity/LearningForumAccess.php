<?php



namespace Formalms\Entity;

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
     * @ORM\Column(name="idForum", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idforum = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idMember", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idmember = '0';


}
