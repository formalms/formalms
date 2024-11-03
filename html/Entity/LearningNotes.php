<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningNotes
 *
 * @ORM\Table(name="learning_notes")
 * @ORM\Entity
 */
class LearningNotes
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idNotes", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idnotes;

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="owner", type="integer", nullable=false)
     */
    private $owner = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $data = null;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="textof", type="string", length=65536, nullable=false)
     */
    private $textof;


}
