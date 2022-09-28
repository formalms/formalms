<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTestChartsConfig
 *
 * @ORM\Table(name="learning_test_charts_config")
 * @ORM\Entity
 */
class LearningTestChartsConfig
{
    /**
     * @var int
     *
     * @ORM\Column(name="idTest", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idtest;

    /**
     * @var int
     *
     * @ORM\Column(name="chart_max_value", type="bigint", nullable=false, options={"default"="100"})
     */
    private $chartMaxValue = '100';

    /**
     * @var int
     *
     * @ORM\Column(name="category_sufficient_threesold", type="bigint", nullable=false)
     */
    private $categorySufficientThreesold = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_category_sufficient_threesold", type="boolean", nullable=false)
     */
    private $showCategorySufficientThreesold = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="test_fields", type="text", length=65535, nullable=true)
     */
    private $testFields;

    /**
     * @var string|null
     *
     * @ORM\Column(name="chart_text", type="text", length=65535, nullable=true)
     */
    private $chartText;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_chart_legend", type="boolean", nullable=false)
     */
    private $showChartLegend = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="show_chart_in_perc", type="boolean", nullable=false)
     */
    private $showChartInPerc = '0';


}
