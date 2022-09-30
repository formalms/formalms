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
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="hteditor", type="string", length=255, nullable=false)
     * @ORM\Id
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
