<?php



namespace FormaLms\Entity;

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
     * @var string
     *
     * @ORM\Column(name="idst", type="string", length=11, nullable=false)
     
     */
    private $idst = '';

    /**
     * @var string
     *
     * @ORM\Column(name="idstAdmin", type="string", length=11, nullable=false)
     
     */
    private $idstadmin = '';


}
