<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningOrganizationAccess
 *
 * @ORM\Table(name="learning_organization_access", indexes={@ORM\Index(name="kind", columns={"kind"}), @ORM\Index(name="idObject", columns={"idOrgAccess"})})
 * @ORM\Entity
 */
class LearningOrganizationAccess
{
    /**
     * @var array
     *
     * @ORM\Column(name="kind", type="simple_array", length=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $kind = '';

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $value = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idOrgAccess", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idorgaccess = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="params", type="string", length=255, nullable=true)
     */
    private $params;


}
