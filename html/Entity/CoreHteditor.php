<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreHteditor
 *
 * @ORM\Table(name="core_hteditor", indexes={
 *     @ORM\Index(name="hteditor_idx", columns={"hteditor"})
 * })
 * @ORM\Entity
 */
class CoreHteditor
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
     * @ORM\Column(name="hteditor", type="string", length=255, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $hteditor = '';

    /**
     * @var string
     *
     * @ORM\Column(name="hteditorname", type="string", length=255, nullable=false)
     */
    private $hteditorname = '';


}
