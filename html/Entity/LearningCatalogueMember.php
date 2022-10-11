<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCatalogueMember
 *
 * @ORM\Table(name="learning_catalogue_member", indexes={
 *     @ORM\Index(name="idst_member_idx", columns={"idst_member"}),
 *     @ORM\Index(name="id_catalogue_idx", columns={"idCatalogue"})
 * })
 * @ORM\Entity
 */
class LearningCatalogueMember
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
     * @ORM\Column(name="idst_member", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstMember = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCatalogue", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcatalogue = '0';


}
