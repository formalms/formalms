<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRestAuthentication
 *
 * @ORM\Table(name="core_rest_authentication", indexes={
 *     @ORM\Index(name="token_idx", columns={"token"})
 * })
 * @ORM\Entity
 */
class CoreRestAuthentication
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
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     
     */
    private $token = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="user_level", type="integer", nullable=false)
     */
    private $userLevel = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generation_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $generationDate = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_enter_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $lastEnterDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $expiryDate = null;


}
