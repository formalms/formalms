<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetenceUser
 *
 * @ORM\Table(name="learning_competence_user", indexes={
 *     @ORM\Index(name="id_competence_idx", columns={"id_competence"}),
 *     @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningCompetenceUser
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
     * @ORM\Column(name="id_competence", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCompetence = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="score_got", type="float", precision=10, scale=0, nullable=false)
     */
    private $scoreGot = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_assign_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $lastAssignDate = '0000-00-00 00:00:00';


}
