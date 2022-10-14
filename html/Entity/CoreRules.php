<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRules
 *
 * @ORM\Table(name="core_rules")
 * @ORM\Entity
 */
class CoreRules
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_rule", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idRule;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=false)
     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="rule_type", type="string", length=10, nullable=false)
     */
    private $ruleType = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="date", nullable=false, options={"default"="0000-00-00"})
     */
    private $creationDate = '0000-00-00';

    /**
     * @var bool
     *
     * @ORM\Column(name="rule_active", type="boolean", nullable=false)
     */
    private $ruleActive = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="course_list", type="text", length=65535, nullable=false)
     */
    private $courseList;


}
