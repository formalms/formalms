<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreSettingGroup
 *
 * @ORM\Table(name="core_setting_group", indexes={
 *      @ORM\Index(name="path_name_idx", columns={"path_name"}),
 *      @ORM\Index(name="idst_idx", columns={"idst"})
 * })
 * @ORM\Entity
 */
class CoreSettingGroup
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
     * @ORM\Column(name="path_name", type="string", length=255, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $pathName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=false)
     */
    private $value;


}
