<?php



namespace Formalms\Entity;

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
     * @ORM\Column(name="log_time", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $logTime = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="applied", type="text", length=65535, nullable=false)
     */
    private $applied;


}
