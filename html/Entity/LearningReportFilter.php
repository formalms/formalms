<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReportFilter
 *
 * @ORM\Table(name="learning_report_filter")
 * @ORM\Entity
 */
class LearningReportFilter
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id_filter", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFilter;

    /**
     * @var int
     *
     * @ORM\Column(name="id_report", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $idReport = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="author", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $author = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $creationDate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="filter_name", type="string", length=255, nullable=false)
     */
    private $filterName = '';

    /**
     * @var string
     * @deprecated Since 4.2.0, use $filterDataJson instead. Will be removed in 5.0.0.
     *             This column stores PHP serialized data which is being migrated to JSON format.
     *
     * @ORM\Column(name="filter_data", type="text", nullable=false)
     */
    private $filterData;

    /**
     * JSON-encoded filter data (replaces the deprecated serialized filter_data column)
     *
     * @var string|null
     *
     * @ORM\Column(name="filter_data_json", type="text", nullable=true)
     */
    private $filterDataJson;

    /**
     * Get filter data (decoded from JSON, with fallback to legacy serialized format)
     *
     * @return array|null
     */
    public function getFilterDataDecoded(): ?array
    {
        // Prefer JSON column
        if (!empty($this->filterDataJson)) {
            $data = json_decode($this->filterDataJson, true);
            if ($data !== null || json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }

        // Fallback to legacy serialized data
        if (!empty($this->filterData)) {
            @trigger_error(
                'Reading from filter_data (serialized) is deprecated since 4.2.0. Data should be migrated to filter_data_json.',
                E_USER_DEPRECATED
            );
            $data = @unserialize($this->filterData);
            if ($data !== false) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Set filter data (encodes to JSON and legacy serialized format for backward compatibility)
     *
     * @param array $data
     * @return self
     */
    public function setFilterDataEncoded(array $data): self
    {
        $this->filterDataJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->filterData = serialize($data); // Legacy - will be removed in 5.0.0

        return $this;
    }

    /**
     * @deprecated Since 4.2.0, use getFilterDataDecoded() instead. Will be removed in 5.0.0.
     * @return string
     */
    public function getFilterData(): string
    {
        @trigger_error(
            sprintf('%s() is deprecated since 4.2.0, use getFilterDataDecoded() instead.', __METHOD__),
            E_USER_DEPRECATED
        );
        return $this->filterData;
    }

    /**
     * @deprecated Since 4.2.0, use setFilterDataEncoded() instead. Will be removed in 5.0.0.
     * @param string $filterData
     * @return self
     */
    public function setFilterData(string $filterData): self
    {
        @trigger_error(
            sprintf('%s() is deprecated since 4.2.0, use setFilterDataEncoded() instead.', __METHOD__),
            E_USER_DEPRECATED
        );
        $this->filterData = $filterData;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilterDataJson(): ?string
    {
        return $this->filterDataJson;
    }

    /**
     * @param string|null $filterDataJson
     * @return self
     */
    public function setFilterDataJson(?string $filterDataJson): self
    {
        $this->filterDataJson = $filterDataJson;

        return $this;
    }

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=false)
     */
    private $isPublic = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="views", type="integer", nullable=false)
     */
    private $views = '0';


}
