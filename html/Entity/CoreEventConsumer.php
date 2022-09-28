<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventConsumer
 *
 * @ORM\Table(name="core_event_consumer", uniqueConstraints={@ORM\UniqueConstraint(name="consumer_class", columns={"consumer_class"})})
 * @ORM\Entity
 */
class CoreEventConsumer
{
    /**
     * @var int
     *
     * @ORM\Column(name="idConsumer", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idconsumer;

    /**
     * @var string
     *
     * @ORM\Column(name="consumer_class", type="string", length=50, nullable=false)
     */
    private $consumerClass = '';

    /**
     * @var string
     *
     * @ORM\Column(name="consumer_file", type="string", length=255, nullable=false)
     */
    private $consumerFile = '';


}
