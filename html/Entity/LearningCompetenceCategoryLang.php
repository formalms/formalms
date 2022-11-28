<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetenceCategoryLang
 *
 * @ORM\Table(name="learning_competence_category_lang")
 * @ORM\Entity
 */
class LearningCompetenceCategoryLang
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_category", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCategory = '0';

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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;


}
