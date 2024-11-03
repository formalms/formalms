<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPrjFiles
 *
 * @ORM\Table(name="learning_prj_files")
 * @ORM\Entity
 */
class LearningPrjFiles
{

    use Timestamps;

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
     * @ORM\Column(name="pid", type="integer", nullable=false)
     */
    private $pid = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="fname", type="string", length=255, nullable=false)
     */
    private $fname = '';

    /**
     * @var string
     *
     * @ORM\Column(name="ftitle", type="string", length=255, nullable=false)
     */
    private $ftitle = '';

    /**
     * @var string
     *
     * @ORM\Column(name="fver", type="string", length=255, nullable=false)
     */
    private $fver = '';

    /**
     * @var string
     *
     * @ORM\Column(name="fdesc", type="string", length=65536, nullable=false)
     */
    private $fdesc;


}
