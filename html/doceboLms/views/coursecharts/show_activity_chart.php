<script type="text/javascript">
	function createActivityChart(id, data) {
		var oDS = new YAHOO.util.DataSource(data);
		oDS.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
		oDS.responseSchema = { fields: ["date", "average", "user"] };

		var operationsAxisLabel = function(value) { return value; };

		var operationsAxis = new YAHOO.widget.NumericAxis();
		operationsAxis.labelFunction = operationsAxisLabel;
		operationsAxis.minimum = 0;
		operationsAxis.title = "<?php echo Lang::t('_NUMBER_OF_OP', 'statistic'); ?>";


		var daysAxis = new YAHOO.widget.CategoryAxis();
		daysAxis.title = "<?php echo Lang::t('_DAYS', 'standard'); ?>";

		var seriesDef = [
			{displayName: "<?php echo Lang::t('_USER', 'course_charts'); ?>", yField: "user", style: {size: 10}},
			{displayName: "<?php echo Lang::t('_AVERANGE', 'course_charts'); ?>", yField: "average", style: {size: 10}}
		];

		var getDataTipText = function(item, index, series) {
			var toolTipText = series.displayName + "\n" + item[series.yField];
			return toolTipText;
		};

		var Chart = new YAHOO.widget.ColumnChart(id, oDS, {
			series: seriesDef,
			xField: "date",
			xAxis: daysAxis,
			yAxis: operationsAxis,
			dataTipFunction: getDataTipText,
			wmode: "opaque",
			style: {
				padding: 20,
				animationEnabled: false,
				border: {color: 0x000000, size: 1},
        font: {name: "Arial", color: 0x000000, size: 12},
				xAxis: {labelRotation: 45},
				legend: {
					display: "bottom",
					padding: 10,
					spacing: 5
				}
			}
		});

		return Chart;
	}

	var $D = YAHOO.util.Dom;
	var $E = YAHOO.util.Event;

	$E.onDOMReady(function(e) {
		$E.addListener("activity_chart_select", "change", function(e) {
			$D.get("id_user").value = this.value;
			$D.get("activity_chart_form").submit();
		});
	});
</script>
<div>
	<?php
		echo Form::getDropdown(Lang::t('_USER', 'course_charts'), 'activity_chart_select', 'activity_chart_select', $users_list, $selected_user);
		echo Form::openForm('activity_chart_form', $form_url);
		echo Form::getHidden('id_user', 'id_user', $selected_user);
		echo Form::closeForm();
	?>
</div>
