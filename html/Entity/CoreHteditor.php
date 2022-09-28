<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreHteditor
 *
 * @ORM\Table(name="core_hteditor")
 * @ORM\Entity
 */
class CoreHteditor
{
    /**
     * @var string
     *
     * @ORM\Column(name="hteditor", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $hteditor = '';

    /**
     * @var string
     *
     * @ORM\Column(name="hteditorname", type="string", length=255, nullable=false)
     */
    private $hteditorname = '';


}
