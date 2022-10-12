<?php



namespace Formalms\Entity;

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
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_certificate", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCertificate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="on_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $onDate = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="cert_file", type="string", length=255, nullable=false)
     */
    private $certFile = '';


}
