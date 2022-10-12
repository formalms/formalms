<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCompetenceLang
 *
 * @ORM\Table(name="learning_competence_lang", indexes={
 *      @ORM\Index(name="id_competence_idx", columns={"id_competence"}), 
 *      @ORM\Index(name="lang_code_idx", columns={"lang_code"})
 * })
 * @ORM\Entity
 */
class LearningCompetenceLang
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
     * @ORM\Column(name="id_competence", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idCompetence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=false)
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
