<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreAdminTree
 *
 * @ORM\Table(name="core_admin_tree", indexes={
 *     @ORM\Index(name="idst_idx", columns={"idst"}),
 *     @ORM\Index(name="idstAdmin_idx", columns={"idstAdmin"})
 * })
 * @ORM\Entity
 */
class CoreAdminTree
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
     * @var string
     *
     * @ORM\Column(name="idst", type="string", length=11, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '';

    /**
     * @var string
     *
     * @ORM\Column(name="idstAdmin", type="string", length=11, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstadmin = '';


}
