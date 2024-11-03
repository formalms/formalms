<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCertificateAssign
 *
 * @ORM\Table(name="learning_certificate_assign", indexes={
 *     @ORM\Index(name="id_certificate_idx", columns={"id_certificate"}),
 *     @ORM\Index(name="id_course_idx", columns={"id_course"}),
 *     @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningCertificateAssign
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
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     
     */
    private $idUser = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="on_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $onDate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="cert_file", type="string", length=255, nullable=false)
     */
    private $certFile = '';


}
