<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCourseDateUser
 *
 * @ORM\Table(name="learning_course_date_user", indexes={
 *      @ORM\Index(name="id_date_idx", columns={"id_date"}), 
 *      @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningCourseDateUser
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
     * @ORM\Column(name="id_date", type="integer", nullable=false)
     
     */
    private $idDate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     
     */
    private $idUser = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_subscription", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateSubscription = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_complete", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateComplete = '0000-00-00 00:00:00';

    /**
     * @var string|null
     *
     * @ORM\Column(name="presence", type="text", length=16777215, nullable=true)
     */
    private $presence;

    /**
     * @var int
     *
     * @ORM\Column(name="subscribed_by", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $subscribedBy = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="overbooking", type="integer", nullable=true)
     */
    private $overbooking = '0';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="requesting_unsubscribe", type="boolean", nullable=true)
     */
    private $requestingUnsubscribe;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="requesting_unsubscribe_date", type="datetime", nullable=true)
     */
    private $requestingUnsubscribeDate;


}
