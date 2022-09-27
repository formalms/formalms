<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CorePrivacypolicy
 *
 * @ORM\Table(name="core_privacypolicy")
 * @ORM\Entity
 */
class CorePrivacypolicy
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_policy", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPolicy;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var int
     *
     * @ORM\Column(name="is_default", type="integer", nullable=false)
     */
    private $isDefault = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastedit_date", type="datetime", nullable=false)
     */
    private $lasteditDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="validity_date", type="datetime", nullable=false)
     */
    private $validityDate;


}
