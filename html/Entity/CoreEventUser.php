<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventUser
 *
 * @ORM\Table(name="core_event_user", indexes={
 *     @ORM\Index(name="idst_idx", columns={"idst"}),
 *     @ORM\Index(name="id_event_mgr_idx", columns={"idEventMgr"})
 * })
 * @ORM\Entity
 */
class CoreEventUser
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
     * @ORM\Column(name="idst", type="integer", nullable=false)
     
     */
    private $idst = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idEventMgr", type="integer", nullable=false)
     
     */
    private $ideventmgr = '0';

    /**
     * @var array
     *
     * @ORM\Column(name="channel", type="simple_array", length=0, nullable=false)
     */
    private $channel = '';


}
