<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLabel
 *
 * @ORM\Table(name="learning_label")
 * @ORM\Entity
 */
class LearningLabel
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_common_label", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCommonLabel = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=0, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=false)
     */
    private $fileName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $sequence = '0';


}
