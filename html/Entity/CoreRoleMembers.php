<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRoleMembers
 *
 * @ORM\Table(name="core_role_members", uniqueConstraints={@ORM\UniqueConstraint(name="idst", columns={"idst", "idstMember"})})
 * @ORM\Entity
 */
class CoreRoleMembers
{

    use Timestamps;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
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
     * @ORM\Column(name="idstMember", type="integer", nullable=false)
     */
    private $idstmember = '0';


}
