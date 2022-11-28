<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTeacherProfile
 *
 * @ORM\Table(name="learning_teacher_profile")
 * @ORM\Entity
 */
class LearningTeacherProfile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="curriculum", type="text", length=65535, nullable=false)
     */
    private $curriculum;

    /**
     * @var string
     *
     * @ORM\Column(name="publications", type="text", length=65535, nullable=false)
     */
    private $publications;


}
