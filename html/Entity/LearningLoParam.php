<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLoParam
 *
 * @ORM\Table(name="learning_lo_param", uniqueConstraints={@ORM\UniqueConstraint(name="idParam_name", columns={"idParam", "param_name"})})
 * @ORM\Entity
 */
class LearningLoParam
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idParam", type="integer", nullable=false)
     */
    private $idparam = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="param_name", type="string", length=20, nullable=false)
     */
    private $paramName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="param_value", type="string", length=255, nullable=false)
     */
    private $paramValue = '';


}
