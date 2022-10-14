<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningScormItemsTrack
 *
 * @ORM\Table(name="learning_scorm_items_track", indexes={@ORM\Index(name="idscorm_item", columns={"idscorm_item"}), @ORM\Index(name="idUser", columns={"idUser"}), @ORM\Index(name="idscorm_tracking", columns={"idscorm_tracking"}), @ORM\Index(name="idscorm_organization", columns={"idscorm_organization"})})
 * @ORM\Entity
 */
class LearningScormItemsTrack
{
    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_item_track", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idscormItemTrack;

    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_organization", type="integer", nullable=false)
     */
    private $idscormOrganization = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="idscorm_item", type="integer", nullable=true)
     */
    private $idscormItem;

    /**
     * @var int
     *
     * @ORM\Column(name="idReference", type="integer", nullable=false)
     */
    private $idreference = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="idscorm_tracking", type="integer", nullable=true)
     */
    private $idscormTracking;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false, options={"default"="not attempted"})
     */
    private $status = 'not attempted';

    /**
     * @var int
     *
     * @ORM\Column(name="nChild", type="integer", nullable=false)
     */
    private $nchild = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="nChildCompleted", type="integer", nullable=false)
     */
    private $nchildcompleted = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="nDescendant", type="integer", nullable=false)
     */
    private $ndescendant = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="nDescendantCompleted", type="integer", nullable=false)
     */
    private $ndescendantcompleted = '0';


}
