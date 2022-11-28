<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRulesEntity
 *
 * @ORM\Table(name="core_rules_entity")
 * @ORM\Entity
 */
class CoreRulesEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_rule", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idRule = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="id_entity", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEntity = '';

    /**
     * @var string
     *
     * @ORM\Column(name="course_list", type="text", length=65535, nullable=false)
     */
    private $courseList;


}
