<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRole
 *
 * @ORM\Table(name="core_role", indexes={
 *      @ORM\Index(name="idPlugin_idx", columns={"idPlugin"}), 
 *      @ORM\Index(name="roleid_idx", columns={"roleid"}),
 *      @ORM\Index(name="idst_idx", columns={"idst"})
 * })
 * @ORM\Entity
 */
class CoreRole
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
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     
     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="roleid", type="string", length=255, nullable=false)
     */
    private $roleid = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var int|null
     *
     * @ORM\Column(name="idPlugin", type="integer", nullable=true)
     */
    private $idplugin;


}
