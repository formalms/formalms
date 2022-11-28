<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCustomfieldSon
 *
 * @ORM\Table(name="core_customfield_son")
 * @ORM\Entity
 */
class CoreCustomfieldSon
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_field_son", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFieldSon;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_field", type="integer", nullable=false)
     */
    private $idField = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';


}
