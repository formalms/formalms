<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * LearningCompetenceCategoryLang
 *
 * @ORM\Table(name="learning_competence_category_lang", indexes={
 *     @ORM\Index(name="id_category_idx", columns={"id_category"}),
 *     @ORM\Index(name="lang_code_idx", columns={"lang_code"})
 * })
 * @ORM\Entity
 */
class LearningCompetenceCategoryLang
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
     * @ORM\Column(name="id_category", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idCategory = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=false)

     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
     */
    private $description;


}
