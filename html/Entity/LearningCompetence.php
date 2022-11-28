<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetence
 *
 * @ORM\Table(name="learning_competence")
 * @ORM\Entity
 */
class LearningCompetence
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_competence", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCompetence;

    /**
     * @var int
     *
     * @ORM\Column(name="id_category", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idCategory = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=0, nullable=false, options={"default"="score"})
     */
    private $type = 'score';

    /**
     * @var float
     *
     * @ORM\Column(name="score", type="float", precision=10, scale=0, nullable=false)
     */
    private $score = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="typology", type="string", length=0, nullable=false, options={"default"="skill"})
     */
    private $typology = 'skill';

    /**
     * @var int
     *
     * @ORM\Column(name="expiration", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $expiration = '0';


}
