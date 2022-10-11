<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreTagRelation
 *
 * @ORM\Table(name="core_tag_relation", indexes={
 *     @ORM\Index(name="id_tag_idx", columns={"id_tag"}),
 *     @ORM\Index(name="id_resource_idx", columns={"id_resource"}),
 *     @ORM\Index(name="resource_type_idx", columns={"resource_type"}),
 *     @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class CoreTagRelation
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
     * @ORM\Column(name="id_tag", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idTag = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_resource", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idResource = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="resource_type", type="string", length=255, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $resourceType = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="private", type="boolean", nullable=false)
     */
    private $private = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     */
    private $idCourse = '0';


}
