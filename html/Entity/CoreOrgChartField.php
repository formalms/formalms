<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreOrgChartField
 *
 * @ORM\Table(name="core_org_chart_field")
 * @ORM\Entity
 */
class CoreOrgChartField
{
    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="id_field", type="string", length=11, nullable=false)
     * @ORM\Id
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


}
