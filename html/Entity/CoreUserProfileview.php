<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreUserProfileview
 *
 * @ORM\Table(name="core_user_profileview", indexes={
 *     @ORM\Index(name="id_owner_idx", columns={"id_owner"}),
 *     @ORM\Index(name="id_viewer_idx", columns={"id_viewer"})
 * })
 * @ORM\Entity
 */
class CoreUserProfileview
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
     * @ORM\Column(name="id_owner", type="integer", nullable=false)
     
     */
    private $idOwner = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_viewer", type="integer", nullable=false)
     
     */
    private $idViewer = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_view", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $dateView = null;


}
