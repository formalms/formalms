<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreOrgChartField
 *
 * @ORM\Table(name="core_org_chart_field", indexes={
 *     @ORM\Index(name="idst_idx", columns={"idst"}),
 *     @ORM\Index(name="id_field_idx", columns={"id_field"}),
 * })
 * @ORM\Entity
 */
class CoreOrgChartField
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
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     
     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="id_field", type="string", length=11, nullable=false)
     
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
