<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventConsumerClass
 *
 * @ORM\Table(name="core_event_consumer_class", indexes={
 *     @ORM\Index(name="id_consumer_idx", columns={"idConsumer"}),
 *     @ORM\Index(name="id_class_idx", columns={"idClass"})
 * })
 * @ORM\Entity
 */
class CoreEventConsumerClass
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
     * @var int
     *
     * @ORM\Column(name="idConsumer", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idconsumer = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idClass", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idclass = '0';


}
