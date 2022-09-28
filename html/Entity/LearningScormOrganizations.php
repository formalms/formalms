<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningScormOrganizations
 *
 * @ORM\Table(name="learning_scorm_organizations", uniqueConstraints={@ORM\UniqueConstraint(name="idsco_package_unique", columns={"org_identifier", "idscorm_package"})})
 * @ORM\Entity
 */
class LearningScormOrganizations
{
    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_organization", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idscormOrganization;

    /**
     * @var string
     *
     * @ORM\Column(name="org_identifier", type="string", length=255, nullable=false)
     */
    private $orgIdentifier = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_package", type="integer", nullable=false)
     */
    private $idscormPackage = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=true)
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(name="nChild", type="integer", nullable=false)
     */
    private $nchild = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="nDescendant", type="integer", nullable=false)
     */
    private $ndescendant = '0';


}
