<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLightRepoFiles
 *
 * @ORM\Table(name="learning_light_repo_files")
 * @ORM\Entity
 */
class LearningLightRepoFiles
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_file", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFile;

    /**
     * @var int
     *
     * @ORM\Column(name="id_repository", type="integer", nullable=false)
     */
    private $idRepository = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=false)
     */
    private $fileName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="file_descr", type="text", length=65535, nullable=false)
     */
    private $fileDescr;

    /**
     * @var int
     *
     * @ORM\Column(name="id_author", type="integer", nullable=false)
     */
    private $idAuthor = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="post_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $postDate = null;


}
