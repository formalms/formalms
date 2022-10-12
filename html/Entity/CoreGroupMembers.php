<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreGroupMembers
 *
 * @ORM\Table(name="core_group_members", uniqueConstraints={@ORM\UniqueConstraint(name="unique_relation", columns={"idst", "idstMember"})})
 * @ORM\Entity
 */
class CoreGroupMembers
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

    /**
     * @var string
     *
     * @ORM\Column(name="filter", type="string", length=50, nullable=false)
     */
    private $filter = '';


}
