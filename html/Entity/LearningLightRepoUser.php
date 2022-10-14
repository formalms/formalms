<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLightRepoUser
 *
 * @ORM\Table(name="learning_light_repo_user", indexes={
 *      @ORM\Index(name="id_repo_idx", columns={"id_repo"}),
 *      @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningLightRepoUser
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_repo", type="integer", nullable=false)
     
     */
    private $idRepo = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     
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
