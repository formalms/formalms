<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCoursepath
 *
 * @ORM\Table(name="learning_coursepath")
 * @ORM\Entity
 */
class LearningCoursepath
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_path", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPath;

    /**
     * @var string
     *
     * @ORM\Column(name="path_code", type="string", length=255, nullable=false)
     */
    private $pathCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="path_name", type="string", length=255, nullable=false)
     */
    private $pathName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="path_descr", type="text", length=65535, nullable=false)
     */
    private $pathDescr;

    /**
     * @var bool
     *
     * @ORM\Column(name="subscribe_method", type="boolean", nullable=false)
     */
    private $subscribeMethod = '0';


}
