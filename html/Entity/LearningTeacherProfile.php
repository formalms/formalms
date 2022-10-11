<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTeacherProfile
 *
 * @ORM\Table(name="learning_teacher_profile", indexes={
 *      @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningTeacherProfile
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
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
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
