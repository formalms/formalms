<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCalendar
 *
 * @ORM\Table(name="core_calendar")
 * @ORM\Entity
 */
class CoreCalendar
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
     * @var string|null
     *
     * @ORM\Column(name="class", type="string", length=30, nullable=true)
     */
    private $class;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="create_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $createDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $startDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $endDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="private", type="string", length=2, nullable=true)
     */
    private $private;

    /**
     * @var string|null
     *
     * @ORM\Column(name="category", type="string", length=255, nullable=true)
     */
    private $category;

    /**
     * @var int|null
     *
     * @ORM\Column(name="type", type="bigint", nullable=true)
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(name="visibility_rules", type="text", length=255, nullable=true)
     */
    private $visibilityRules;

    /**
     * @var int|null
     *
     * @ORM\Column(name="_owner", type="integer", nullable=true)
     */
    private $owner;

    /**
     * @var int|null
     *
     * @ORM\Column(name="_day", type="smallint", nullable=true)
     */
    private $day;

    /**
     * @var int|null
     *
     * @ORM\Column(name="_month", type="smallint", nullable=true)
     */
    private $month;

    /**
     * @var int|null
     *
     * @ORM\Column(name="_year", type="smallint", nullable=true)
     */
    private $year;


}
