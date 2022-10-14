<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreMenu
 *
 * @ORM\Table(name="core_menu")
 * @ORM\Entity
 */
class CoreMenu
{
    /**
     * @var int
     *
     * @ORM\Column(name="idMenu", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmenu;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=false)
     */
    private $image = '';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="is_active", type="string", length=0, nullable=false, options={"default"="true"})
     */
    private $isActive = 'true';

    /**
     * @var string
     *
     * @ORM\Column(name="collapse", type="string", length=0, nullable=false, options={"default"="true"})
     */
    private $collapse = 'true';

    /**
     * @var int|null
     *
     * @ORM\Column(name="idParent", type="integer", nullable=true)
     */
    private $idparent;

    /**
     * @var int|null
     *
     * @ORM\Column(name="idPlugin", type="integer", nullable=true)
     */
    private $idplugin;

    /**
     * @var string
     *
     * @ORM\Column(name="of_platform", type="string", length=255, nullable=false, options={"default"="framework"})
     */
    private $ofPlatform = 'framework';


}
