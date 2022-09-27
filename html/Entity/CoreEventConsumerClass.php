<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventConsumerClass
 *
 * @ORM\Table(name="core_event_consumer_class")
 * @ORM\Entity
 */
class CoreEventConsumerClass
{
    /**
     * @var int
     *
     * @ORM\Column(name="idConsumer", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idconsumer = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idClass", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idclass = '0';


}
