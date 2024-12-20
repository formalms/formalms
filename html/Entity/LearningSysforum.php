<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningSysforum
 *
 * @ORM\Table(name="learning_sysforum")
 * @ORM\Entity
 */
class LearningSysforum
{
    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idMessage", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmessage;

    /**
     * @var string
     *
     * @ORM\Column(name="key1", type="string", length=255, nullable=false)
     */
    private $key1 = '';

    /**
     * @var int
     *
     * @ORM\Column(name="key2", type="integer", nullable=false)
     */
    private $key2 = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="key3", type="integer", nullable=true)
     */
    private $key3;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="textof", type="string", length=65536, nullable=false)
     */
    private $textof;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="posted", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $posted = null;

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false)
     */
    private $author = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="attach", type="string", length=255, nullable=false)
     */
    private $attach = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="locked", type="boolean", nullable=false)
     */
    private $locked = '0';


}
