<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEvent
 *
 * @ORM\Table(name="core_event", indexes={@ORM\Index(name="idClass", columns={"idClass"})})
 * @ORM\Entity
 */
class CoreEvent
{
    /**
     * @var int
     *
     * @ORM\Column(name="idEvent", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idevent;

    /**
     * @var int
     *
     * @ORM\Column(name="idClass", type="integer", nullable=false)
     */
    private $idclass = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=50, nullable=false)
     */
    private $module = '';

    /**
     * @var string
     *
     * @ORM\Column(name="section", type="string", length=50, nullable=false)
     */
    private $section = '';

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="smallint", nullable=false, options={"default"="1289","unsigned"=true})
     */
    private $priority = '1289';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description = '';


}
