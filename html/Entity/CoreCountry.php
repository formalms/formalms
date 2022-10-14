<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCountry
 *
 * @ORM\Table(name="core_country", indexes={@ORM\Index(name="IDX_COUNTRIES_NAME", columns={"name_country"})})
 * @ORM\Entity
 */
class CoreCountry
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id_country", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="name_country", type="string", length=64, nullable=false)
     */
    private $nameCountry = '';

    /**
     * @var string
     *
     * @ORM\Column(name="iso_code_country", type="string", length=3, nullable=false)
     */
    private $isoCodeCountry = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_zone", type="integer", nullable=false)
     */
    private $idZone = '0';


}
