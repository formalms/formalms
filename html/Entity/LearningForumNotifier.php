<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningForumNotifier
 *
 * @ORM\Table(name="learning_forum_notifier", indexes={
 *      @ORM\Index(name="id_notify_idx", columns={"id_notify"}),
 *      @ORM\Index(name="id_user_idx", columns={"id_user"}),
 *      @ORM\Index(name="notify_is_a_idx", columns={"notify_is_a"})
 * })
 * @ORM\Entity
 */
class LearningForumNotifier
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
     * @ORM\Column(name="id_notify", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idNotify = '0';

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
     * @ORM\Column(name="notify_is_a", type="string", length=0, nullable=false, options={"default"="forum"})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $notifyIsA = 'forum';


}
