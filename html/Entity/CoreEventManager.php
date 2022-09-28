<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventManager
 *
 * @ORM\Table(name="core_event_manager", uniqueConstraints={@ORM\UniqueConstraint(name="idClass", columns={"idClass"})})
 * @ORM\Entity
 */
class CoreEventManager
{
    /**
     * @var int
     *
     * @ORM\Column(name="idEventMgr", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $ideventmgr;

    /**
     * @var int
     *
     * @ORM\Column(name="idClass", type="integer", nullable=false)
     */
    private $idclass = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="permission", type="string", length=0, nullable=false, options={"default"="not_used"})
     */
    private $permission = 'not_used';

    /**
     * @var array
     *
     * @ORM\Column(name="channel", type="simple_array", length=0, nullable=false, options={"default"="email"})
     */
    private $channel = 'email';

    /**
     * @var string
     *
     * @ORM\Column(name="recipients", type="string", length=255, nullable=false)
     */
    private $recipients = '';

    /**
     * @var array
     *
     * @ORM\Column(name="show_level", type="simple_array", length=0, nullable=false)
     */
    private $showLevel = '';


}
