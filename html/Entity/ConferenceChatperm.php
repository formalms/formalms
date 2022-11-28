<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceChatperm
 *
 * @ORM\Table(name="conference_chatperm")
 * @ORM\Entity
 */
class ConferenceChatperm
{
    /**
     * @var int
     *
     * @ORM\Column(name="room_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $roomId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $module = '';

    /**
     * @var int
     *
     * @ORM\Column(name="user_idst", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $userIdst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="perm", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $perm = '';


}
