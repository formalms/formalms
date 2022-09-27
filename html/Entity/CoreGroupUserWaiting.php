<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreGroupUserWaiting
 *
 * @ORM\Table(name="core_group_user_waiting")
 * @ORM\Entity
 */
class CoreGroupUserWaiting
{
    /**
     * @var int
     *
     * @ORM\Column(name="idst_group", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstGroup = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstUser = '0';


}
