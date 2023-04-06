<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningOrganizationAccess
 *
 * @ORM\Table(name="learning_organization_access", indexes={
 *      @ORM\Index(name="kind_idx", columns={"kind"}),
 *      @ORM\Index(name="value_idx", columns={"value"})
 * })
 * @ORM\Entity
 */
class LearningOrganizationAccess
{
    /**
     * @var array
     *
     * @ORM\Column(name="kind", type="string", columnDefinition="ENUM('user', 'group')"), length=0, nullable=false)
     
     */
    private $kind = '';

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer", nullable=false)
     
     */
    private $value = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idOrgAccess", type="integer", nullable=false)
     
     */
    private $idorgaccess;

    /**
     * @var string|null
     *
     * @ORM\Column(name="params", type="string", length=255, nullable=true)
     */
    private $params;


}
