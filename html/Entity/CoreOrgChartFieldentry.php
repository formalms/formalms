<?php



namespace FormaLms\Entity;

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
     * @ORM\Column(name="id_common", type="string", length=11, nullable=false)
     
     */
    private $idCommon = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_common_son", type="integer", nullable=false)
     
     */
    private $idCommonSon = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     
     */
    private $idUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="user_entry", type="text", length=65535, nullable=false)
     */
    private $userEntry;


}
