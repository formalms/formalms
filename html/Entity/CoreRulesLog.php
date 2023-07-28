<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRulesLog
 *
 * @ORM\Table(name="core_rules_log")
 * @ORM\Entity
 */
class CoreRulesLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_log", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idLog;

    /**
     * @var string
     *
     * @ORM\Column(name="log_action", type="string", length=255, nullable=false)
     */
    private $logAction = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="log_time", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $logTime = null;

    /**
     * @var string
     *
     * @ORM\Column(name="applied", type="text", length=65535, nullable=false)
     */
    private $applied;


}
