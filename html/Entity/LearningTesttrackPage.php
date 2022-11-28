<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTesttrackPage
 *
 * @ORM\Table(name="learning_testtrack_page")
 * @ORM\Entity
 */
class LearningTesttrackPage
{
    /**
     * @var int
     *
     * @ORM\Column(name="page", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $page = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idtrack = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="display_from", type="datetime", nullable=true)
     */
    private $displayFrom;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="display_to", type="datetime", nullable=true)
     */
    private $displayTo;

    /**
     * @var int
     *
     * @ORM\Column(name="accumulated", type="integer", nullable=false)
     */
    private $accumulated = '0';


}
