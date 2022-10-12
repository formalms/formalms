<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreDbUpgrades
 *
 * @ORM\Table(name="core_db_upgrades")
 * @ORM\Entity
 */
class CoreDbUpgrades
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="script_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $scriptId;

    /**
     * @var string
     *
     * @ORM\Column(name="script_name", type="string", length=255, nullable=false)
     */
    private $scriptName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="script_description", type="text", length=65535, nullable=true)
     */
    private $scriptDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(name="script_version", type="string", length=255, nullable=true)
     */
    private $scriptVersion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="core_version", type="string", length=255, nullable=true)
     */
    private $coreVersion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $creationDate = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="execution_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $executionDate = '0000-00-00 00:00:00';


}
