<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceMenu
 *
 * @ORM\Table(name="conference_menu")
 * @ORM\Entity
 */
class ConferenceMenu
{
    use Timestamps;    
      
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
     * @ORM\Column(name="collapse", type="string", length=0, nullable=false, options={"default"="false"})
     */
    private $collapse = 'false';


}
