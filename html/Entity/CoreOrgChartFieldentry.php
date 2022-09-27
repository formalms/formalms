<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreOrgChartFieldentry
 *
 * @ORM\Table(name="core_org_chart_fieldentry")
 * @ORM\Entity
 */
class CoreOrgChartFieldentry
{
    /**
     * @var string
     *
     * @ORM\Column(name="id_common", type="string", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCommon = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_common_son", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCommonSon = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="user_entry", type="text", length=65535, nullable=false)
     */
    private $userEntry;


}
