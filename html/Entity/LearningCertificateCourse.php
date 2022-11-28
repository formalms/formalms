<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCertificateCourse
 *
 * @ORM\Table(name="learning_certificate_course")
 * @ORM\Entity
 */
class LearningCertificateCourse
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_certificate", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCertificate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCourse = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="available_for_status", type="boolean", nullable=false)
     */
    private $availableForStatus = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="point_required", type="integer", nullable=false)
     */
    private $pointRequired = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="minutes_required", type="integer", nullable=false)
     */
    private $minutesRequired = '0';


}
