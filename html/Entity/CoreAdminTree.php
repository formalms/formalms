<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreAdminTree
 *
 * @ORM\Table(name="core_admin_tree")
 * @ORM\Entity
 */
class CoreAdminTree
{
    /**
     * @var string
     *
     * @ORM\Column(name="idst", type="string", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '';

    /**
     * @var string
     *
     * @ORM\Column(name="idstAdmin", type="string", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstadmin = '';


}
