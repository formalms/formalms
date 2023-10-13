<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * LearningLabel
 *
 * @ORM\Table(name="learning_label", indexes={
 *     @ORM\Index(name="lang_code_idx", columns={"lang_code"}),
 *     @ORM\Index(name="id_common_label_idx", columns={"id_common_label"})
 * })
 * @ORM\Entity
 */
class LearningLabel
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_common_label", type="integer", nullable=false)
     */
    private $idCommonLabel = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=false)
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
