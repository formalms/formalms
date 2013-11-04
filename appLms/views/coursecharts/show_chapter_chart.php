<script type="text/javascript">
	function createChapterChart(id, data) {
		var oDS = new YAHOO.util.DataSource(data);
		oDS.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
		oDS.responseSchema = { fields: ["id", "name", "passed"] };

		var passedAxisLabel = function(value) {
			var output = '';
			if (value==0) output = '<?php echo Lang::t('passed', 'course_charts') ?>';
			if (value==1) output = '<?php echo Lang::t('failed', 'course_charts'); ?>';
			return output;
		};

		var passedAxis = new YAHOO.widget.NumericAxis();
		passedAxis.labelFunction = passedAxisLabel;
		passedAxis.minimum = 0;
		passedAxis.maximum = 1;
		passedAxis.title = "<?php echo Lang::t('passed', 'standard'); ?>";


		var unitsAxis = new YAHOO.widget.CategoryAxis();
		unitsAxis.title = "<?php echo Lang::t('_CHAPTERS', 'standard'); ?>";

		var seriesDef = [
			{displayName: "<?php echo Lang::t('passed', 'course_charts'); ?>", yField: "passed", style: {size: 20}}
		];

		var getDataTipText = function(item, index, series) {
			var value = item[series.yField], output = "";
			if (value==0) output += '<?php echo Lang::t('passed', 'course_charts') ?>';
			if (value==1) output += '<?php echo Lang::t('failed', 'course_charts'); ?>';
			var toolTipText = output;
			return toolTipText;
		};

		var Chart = new YAHOO.widget.ColumnChart(id, oDS, {
			series: seriesDef,
			xField: "name",
			xAxis: unitsAxis,
			yAxis: passedAxis,
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

	var $D = YAHOO.util.Dom;
	var $E = YAHOO.util.Event;

	$E.onDOMReady(function(e) {
		$E.addListener("chapter_chart_select", "change", function(e) {
			$D.get("id_user").value = this.value;
			$D.get("chapter_chart_form").submit();
		});
	});
</script>
<div>
	<?php
		echo Form::getDropdown(Lang::t('_USER', 'course_charts'), 'chapter_chart_select', 'chapter_chart_select', $users_list, $selected_user);
		echo Form::openForm('chapter_chart_form', $form_url);
		echo Form::getHidden('id_user', 'id_user', $selected_user);
		echo Form::closeForm();
	?>
</div>