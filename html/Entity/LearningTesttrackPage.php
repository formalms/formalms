<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTesttrackPage
 *
 * @ORM\Table(name="learning_testtrack_page", indexes={
 *      @ORM\Index(name="page_idx", columns={"page"}),
 *      @ORM\Index(name="id_track_idx", columns={"idTrack"})
 * })
 * @ORM\Entity
 */
class LearningTesttrackPage
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
     * @ORM\Column(name="page", type="integer", nullable=false)
     
     */
    private $page = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     
     */
    private $idtrack = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="display_from", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $displayFrom;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="display_to", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $displayTo;

    /**
     * @var int
     *
     * @ORM\Column(name="accumulated", type="integer", nullable=false)
     */
    private $accumulated = '0';


}
