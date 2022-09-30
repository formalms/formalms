<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreOrgChartFieldentry
 *
 * @ORM\Table(name="core_org_chart_fieldentry", indexes={
 *     @ORM\Index(name="id_common_idx", columns={"id_common"}),
 *     @ORM\Index(name="id_common_son_idx", columns={"id_common_son"}),
 *     @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class CoreOrgChartFieldentry
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
     * @ORM\Column(name="id_common", type="string", length=11, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCommon = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_common_son", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCommonSon = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
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
