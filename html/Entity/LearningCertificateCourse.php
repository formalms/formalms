<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCertificateCourse
 *
 * @ORM\Table(name="learning_certificate_course", indexes={
 *     @ORM\Index(name="id_certificate_idx", columns={"id_certificate"}),
 *     @ORM\Index(name="id_course_idx", columns={"id_course"})
 * })
 * @ORM\Entity
 */
class LearningCertificateCourse
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
     * @ORM\Column(name="id_certificate", type="integer", nullable=false)
     
     */
    private $idCertificate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     
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
