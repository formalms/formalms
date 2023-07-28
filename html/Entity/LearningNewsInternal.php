<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningNewsInternal
 *
 * @ORM\Table(name="learning_news_internal")
 * @ORM\Entity
 */
class LearningNewsInternal
{
    /**
     * @var int
     *
     * @ORM\Column(name="idNews", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idnews;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $publishDate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="short_desc", type="text", length=65535, nullable=false)
     */
    private $shortDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="long_desc", type="text", length=65535, nullable=false)
     */
    private $longDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=100, nullable=false)
     */
    private $language = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="important", type="boolean", nullable=false)
     */
    private $important = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="viewer", type="text", length=0, nullable=false)
     */
    private $viewer;


}
