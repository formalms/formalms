<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCommunicationLang
 *
 * @ORM\Table(name="learning_communication_lang")
 * @ORM\Entity
 */
class LearningCommunicationLang
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="id_comm", type="integer", nullable=true)
     */
    private $idComm;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=true)
     */
    private $langCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;


}
