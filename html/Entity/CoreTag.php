<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreTag
 *
 * @ORM\Table(name="core_tag", indexes={@ORM\Index(name="tag_name", columns={"tag_name"})})
 * @ORM\Entity
 */
class CoreTag
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id_tag", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTag;

    /**
     * @var string
     *
     * @ORM\Column(name="tag_name", type="string", length=255, nullable=false)
     */
    private $tagName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_parent", type="integer", nullable=false)
     */
    private $idParent = '0';


}
