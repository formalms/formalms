<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreGroupFields
 *
 * @ORM\Table(name="core_group_fields", indexes={
 *     @ORM\Index(name="idst_idx", columns={"idst"}),
 *     @ORM\Index(name="id_field_idx", columns={"id_field"})
 * })
 * @ORM\Entity
 */
class CoreGroupFields
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
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_field", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idField = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="mandatory", type="string", length=0, nullable=false, options={"default"="false"})
     */
    private $mandatory = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="useraccess", type="string", length=0, nullable=false, options={"default"="readonly"})
     */
    private $useraccess = 'readonly';

    /**
     * @var bool
     *
     * @ORM\Column(name="user_inherit", type="boolean", nullable=false)
     */
    private $userInherit = '0';


}
