<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreUserProfileview
 *
 * @ORM\Table(name="core_user_profileview")
 * @ORM\Entity
 */
class CoreUserProfileview
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_owner", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idOwner = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_viewer", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idViewer = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_view", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateView = '0000-00-00 00:00:00';


}
