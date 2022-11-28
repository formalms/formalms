<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAdviceuser
 *
 * @ORM\Table(name="learning_adviceuser")
 * @ORM\Entity
 */
class LearningAdviceuser
{
    /**
     * @var int
     *
     * @ORM\Column(name="idAdvice", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idadvice = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $iduser = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="archivied", type="boolean", nullable=false)
     */
    private $archivied = '0';


}
