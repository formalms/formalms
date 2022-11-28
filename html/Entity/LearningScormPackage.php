<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningScormPackage
 *
 * @ORM\Table(name="learning_scorm_package", indexes={@ORM\Index(name="idUser", columns={"idUser"})})
 * @ORM\Entity
 */
class LearningScormPackage
{
    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_package", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idscormPackage;

    /**
     * @var string
     *
     * @ORM\Column(name="idpackage", type="string", length=255, nullable=false)
     */
    private $idpackage = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idProg", type="integer", nullable=false)
     */
    private $idprog = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=false)
     */
    private $path = '';

    /**
     * @var string
     *
     * @ORM\Column(name="defaultOrg", type="string", length=255, nullable=false)
     */
    private $defaultorg = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="scormVersion", type="string", length=10, nullable=false, options={"default"="1.2"})
     */
    private $scormversion = '1.2';


}
