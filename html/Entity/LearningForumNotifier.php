<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningForumNotifier
 *
 * @ORM\Table(name="learning_forum_notifier")
 * @ORM\Entity
 */
class LearningForumNotifier
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_notify", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idNotify = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="notify_is_a", type="string", length=0, nullable=false, options={"default"="forum"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $notifyIsA = 'forum';


}
