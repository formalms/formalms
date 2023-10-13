<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * LearningTesttrackQuest
 *
 * @ORM\Table(name="learning_testtrack_quest", indexes={
 *      @ORM\Index(name="id_track_idx", columns={"idTrack"}),
 *      @ORM\Index(name="id_quest_idx", columns={"idQuest"})
 * })
 * @ORM\Entity
 */
class LearningTesttrackQuest
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
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     */
    private $idtrack = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idQuest", type="integer", nullable=false)
     */
    private $idquest = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="page", type="integer", nullable=false)
     */
    private $page = '0';


}
