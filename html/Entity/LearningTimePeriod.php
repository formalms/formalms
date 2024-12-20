<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTimePeriod
 *
 * @ORM\Table(name="learning_time_period")
 * @ORM\Entity
 */
class LearningTimePeriod
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id_period", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPeriod;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date", nullable=true, options={"default"=NULL})
     */
    private $startDate = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true, options={"default"=NULL})
     */
    private $endDate = null;


}
