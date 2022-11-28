<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventUser
 *
 * @ORM\Table(name="core_event_user")
 * @ORM\Entity
 */
class CoreEventUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idEventMgr", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $ideventmgr = '0';

    /**
     * @var array
     *
     * @ORM\Column(name="channel", type="simple_array", length=0, nullable=false)
     */
    private $channel = '';


}
