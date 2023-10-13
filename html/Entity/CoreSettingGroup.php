<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * CoreSettingGroup
 *
 * @ORM\Table(name="core_setting_group", indexes={
 *     @ORM\Index(name="idst_idx", columns={"idst"}),
 *     @ORM\Index(name="path_name_idx", columns={"path_name"})
 * })
 * @ORM\Entity
 */
class CoreSettingGroup
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
     * @var string
     *
     * @ORM\Column(name="path_name", type="string", length=255, nullable=false)
     */
    private $pathName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)

     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=65536, nullable=false)
     */
    private $value;


}
