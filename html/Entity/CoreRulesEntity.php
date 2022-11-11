<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRulesEntity
 *
 * @ORM\Table(name="core_rules_entity", indexes={
 *      @ORM\Index(name="id_rule_idx", columns={"id_rule"}),
 *      @ORM\Index(name="id_entity_idx", columns={"id_entity"})
 * })
 * @ORM\Entity
 */
class CoreRulesEntity
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_rule", type="integer", nullable=false)
     
     */
    private $idRule = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="id_entity", type="string", length=50, nullable=false)
     
     */
    private $idEntity = '';

    /**
     * @var string
     *
     * @ORM\Column(name="course_list", type="text", length=65535, nullable=false)
     */
    private $courseList;


}
