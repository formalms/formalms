<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DashboardPermission
 *
 * @ORM\Table(name="dashboard_permission")
 * @ORM\Entity
 */
class DashboardPermission
{

    use Timestamps;


    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_dashboard", type="integer", nullable=false)
     */
    private $idDashboard;

    /**
     * @var string
     *
     * @ORM\Column(name="idst_list", type="string", length=65536, nullable=false)
     */
    private $idstList;


}
