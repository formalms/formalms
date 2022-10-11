<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLoTypes
 *
 * @ORM\Table(name="learning_lo_types", indexes={
 *      @ORM\Index(name="object_type_idx", columns={"objectType"})
 * })
 * @ORM\Entity
 */
class LearningLoTypes
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
     * @var string
     *
     * @ORM\Column(name="objectType", type="string", length=20, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $objecttype = '';

    /**
     * @var string
     *
     * @ORM\Column(name="className", type="string", length=20, nullable=false)
     */
    private $classname = '';

    /**
     * @var string
     *
     * @ORM\Column(name="fileName", type="string", length=50, nullable=false)
     */
    private $filename = '';

    /**
     * @var string
     *
     * @ORM\Column(name="classNameTrack", type="string", length=255, nullable=false)
     */
    private $classnametrack = '';

    /**
     * @var string
     *
     * @ORM\Column(name="fileNameTrack", type="string", length=255, nullable=false)
     */
    private $filenametrack = '';


}
