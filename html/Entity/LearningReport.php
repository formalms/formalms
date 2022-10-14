<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReport
 *
 * @ORM\Table(name="learning_report")
 * @ORM\Entity
 */
class LearningReport
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_report", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idReport;

    /**
     * @var string
     *
     * @ORM\Column(name="report_name", type="string", length=255, nullable=false)
     */
    private $reportName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_name", type="string", length=255, nullable=false)
     */
    private $className = '';

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=false)
     */
    private $fileName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="use_user_selection", type="string", length=0, nullable=false, options={"default"="true"})
     */
    private $useUserSelection = 'true';

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = '0';


}
