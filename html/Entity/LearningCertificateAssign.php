<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCertificateAssign
 *
 * @ORM\Table(name="learning_certificate_assign")
 * @ORM\Entity
 */
class LearningCertificateAssign
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
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\Id
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
