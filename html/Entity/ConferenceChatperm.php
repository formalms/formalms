<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceChatperm
 *
 * @ORM\Table(name="conference_chatperm", indexes={
 *     @ORM\Index(name="room_idx", columns={"room_id"}),
 *     @ORM\Index(name="module_idx", columns={"module"}),
 *     @ORM\Index(name="user_idst_idx", columns={"user_idst"}),
 *     @ORM\Index(name="perm_idx", columns={"perm"})
 * })
 * @ORM\Entity
 */
class ConferenceChatperm
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
     * @ORM\Column(name="room_id", type="integer", nullable=false)
     
     */
    private $roomId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=50, nullable=false)
     
     */
    private $module = '';

    /**
     * @var int
     *
     * @ORM\Column(name="user_idst", type="integer", nullable=false)
     
     */
    private $userIdst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="perm", type="string", length=50, nullable=false)
     
     */
    private $perm = '';


}
