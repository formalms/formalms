<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreGroupUserWaiting
 *
 * @ORM\Table(name="core_group_user_waiting", indexes={
 *     @ORM\Index(name="idst_group_idx", columns={"idst_group"}),
 *     @ORM\Index(name="idst_user_idx", columns={"idst_user"})
 * })
 * @ORM\Entity
 */
class CoreGroupUserWaiting
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
     * @ORM\Column(name="idst_group", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstGroup = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst_user", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstUser = '0';


}
