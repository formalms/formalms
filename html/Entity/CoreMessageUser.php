<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * CoreMessageUser
 *
 * @ORM\Table(name="core_message_user", indexes={
 *     @ORM\Index(name="id_user_idx", columns={"idMessage"}),
 *     @ORM\Index(name="id_user_idx", columns={"idUser"}),
 *     @ORM\Index(name="id_message_idx", columns={"idMessage"})
 * })
 * @ORM\Entity
 */
class CoreMessageUser
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
     * @ORM\Column(name="idMessage", type="integer", nullable=false)
     */
    private $idmessage = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="read", type="boolean", nullable=false)
     */
    private $read = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     */
    private $deleted = '0';


}
