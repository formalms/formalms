<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTestquestExtra
 *
 * @ORM\Table(name="learning_testquest_extra")
 * @ORM\Entity
 */
class LearningTestquestExtra
{
    /**
     * @var int
     *
     * @ORM\Column(name="idQuest", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idquest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idAnswer", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idanswer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="extra_info", type="text", length=65535, nullable=false)
     */
    private $extraInfo;


}
