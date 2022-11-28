<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLightRepoUser
 *
 * @ORM\Table(name="learning_light_repo_user")
 * @ORM\Entity
 */
class LearningLightRepoUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_repo", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idRepo = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_enter", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $lastEnter = '0000-00-00 00:00:00';

    /**
     * @var bool
     *
     * @ORM\Column(name="repo_lock", type="boolean", nullable=false)
     */
    private $repoLock = '0';


}
