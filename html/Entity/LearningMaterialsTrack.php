<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningMaterialsTrack
 *
 * @ORM\Table(name="learning_materials_track")
 * @ORM\Entity
 */
class LearningMaterialsTrack
{
    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idtrack;

    /**
     * @var int
     *
     * @ORM\Column(name="idResource", type="integer", nullable=false)
     */
    private $idresource = '0';

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


}
