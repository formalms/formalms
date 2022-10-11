<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventClass
 *
 * @ORM\Table(name="core_event_class", 
 *      uniqueConstraints={@ORM\UniqueConstraint(name="class_2", columns={"class"})
 * })
 * @ORM\Entity
 */
class CoreEventClass
{
    /**
     * @var int
     *
     * @ORM\Column(name="idClass", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idclass;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=50, nullable=false)
     */
    private $class = '';

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=50, nullable=false)
     */
    private $platform = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;


}
