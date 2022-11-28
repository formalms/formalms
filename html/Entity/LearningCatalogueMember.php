<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCatalogueMember
 *
 * @ORM\Table(name="learning_catalogue_member")
 * @ORM\Entity
 */
class LearningCatalogueMember
{
    /**
     * @var int
     *
     * @ORM\Column(name="idst_member", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idstMember = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCatalogue", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcatalogue = '0';


}
