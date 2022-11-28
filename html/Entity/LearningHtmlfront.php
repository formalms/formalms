<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningHtmlfront
 *
 * @ORM\Table(name="learning_htmlfront")
 * @ORM\Entity
 */
class LearningHtmlfront
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="textof", type="text", length=65535, nullable=false)
     */
    private $textof;


}
