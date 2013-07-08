<script type="text/javascript">
	function createPassedChart(id, data) {
		var oDS = new YAHOO.util.DataSource(data);
		oDS.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
		oDS.responseSchema = { fields: ["id", "name", "passed"] };

		var percentageAxisLabel = function(value) { return value+"%"; };

		var percentageAxis = new YAHOO.widget.NumericAxis();
		percentageAxis.labelFunction = percentageAxisLabel;
		percentageAxis.minimum = 0;
		percentageAxis.maximum = 100;
		percentageAxis.title = "<?php echo Lang::t('_PERCENTAGE', 'standard'); ?>";


		var unitsAxis = new YAHOO.widget.CategoryAxis();
		unitsAxis.title = "<?php echo Lang::t('_CHAPTERS', 'standard'); ?>";

		var seriesDef = [
			{displayName: "<?php echo Lang::t('passed', 'course_charts'); ?>", yField: "passed", style: {size: 20}}
		];

		var getDataTipText = function(item, index, series) {
			var toolTipText = series.displayName + ": " + item[series.yField] + " %";
			return toolTipText;
		};

		var Chart = new YAHOO.widget.ColumnChart(id, oDS, {
			series: seriesDef,
			xField: "name",
			xAxis: unitsAxis,
			yAxis: percentageAxis,
			dataTipFunction: getDataTipText,
			wmode: "opaque",
			style: {
				padding: 20,
				animationEnabled: false,
				border: {color: 0x000000, size: 1},
        font: {name: "Arial", color: 0x000000, size: 12},
				xAxis: {labelRotation: 45}
			}
		});

		return Chart;
	}
</script>
