<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * LearningCourseEditionsUser
 *
 * @ORM\Table(name="learning_course_editions_user", indexes={
 *     @ORM\Index(name="id_user_idx", columns={"id_user"}),
 *     @ORM\Index(name="id_edition_idx", columns={"id_edition"})
 * })
 * @ORM\Entity
 */
class LearningCourseEditionsUser
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_edition", type="integer", nullable=false)
     */
    private $idEdition = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_subscription", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $dateSubscription = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_complete", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $dateComplete = null;

    /**
     * @var int
     *
     * @ORM\Column(name="subscribed_by", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $subscribedBy = '0';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="requesting_unsubscribe", type="boolean", nullable=true)
     */
    private $requestingUnsubscribe;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="requesting_unsubscribe_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $requestingUnsubscribeDate;


}
