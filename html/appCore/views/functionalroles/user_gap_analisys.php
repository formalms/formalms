<?php echo getTitleArea($title_arr); ?>
<script type="text/javascript">
var UserGapAnalisys = {

	gotFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = oRecord.getData("type") == 'flag'
			? (oData > 0 ? '<span class="ico-sprite subs_actv"><span><?php echo Lang::t('_COMPETENCE_OBTAINED', 'competences'); ?></span></span>' : '')
			: oData;
	},

	reqFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = oRecord.getData("type") == 'flag'
			? '<span class="ico-sprite subs_actv"><span><?php echo Lang::t('_COMPETENCE_OBTAINED', 'competences'); ?></span></span>'
			: oData;
	},

	gapFormatter: function(elLiner, oRecord, oColumn, oData) {
		if (oRecord.getData("type") == 'flag') {
			elLiner.innerHTML = '';
			return;
		}
		elLiner.innerHTML = oData <= 0
			? '<b class="green">'+Math.abs(oData)+'</b>'//? '<span class="ico-sprite subs_actv"><span><?php ?></span></span>'
			: '<b class=red>- '+oData+'</b>';
	},

	expireFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = (oRecord.getData("is_expired")
			? '<span class="ico-sprite fd_notice"><span><?php ?></span></span>&nbsp;<b class="red">'+oData+'</b>'
			: oData);
	},

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};
		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
		UserGapAnalisys.sort = sort;
        UserGapAnalisys.dir = dir;
        return "&results=" + results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir+
				"&id_fncrole=" + <?php echo (int)$id_fncrole; ?> +
				"&id_user=" + <?php echo (int)$id_user; ?>;
	},
    exportCSV: function(e) {
        YAHOO.util.Event.preventDefault(e);
        window.open("index.php?r=adm/functionalroles/export_user_gap&id_fncrole=" + <?php echo (int)$id_fncrole; ?> +"&id_user=" + <?php echo (int)$id_user; ?>+"&format=csv&sort=" + UserGapAnalisys.sort + "&dir=" + UserGapAnalisys.dir);
    },
    exportXLS: function(e) {
        YAHOO.util.Event.preventDefault(e);
        window.open("index.php?r=adm/functionalroles/export_user_gap&id_fncrole=" + <?php echo (int)$id_fncrole; ?> +"&id_user=" + <?php echo (int)$id_user; ?>+"&format=xls&sort=" + UserGapAnalisys.sort + "&dir=" + UserGapAnalisys.dir);
   },

	oChart: null,
	initChart: function(oTable) {
		var data = [<?php
			$_arr = array();
			foreach ($chart_data as $record) {
				$_arr[] = '{competence: '.$record['competence'].', '
					.'score_got: '.($record['score_got'] - $record['gap_positive']).', '
					.'gap_negative: '.$record['gap_negative'].', '
					.'gap_positive: '.$record['gap_positive'].', '
					.'gap_percent_negative: '.($record['gap_percent'] <  0 ? $record['gap_percent'] : 0).', '
					.'gap_percent_positive: '.($record['gap_percent'] >= 0 ? $record['gap_percent'] : 0).'}';
			}
			if (!empty($_arr)) echo implode(",", $_arr);
		?>];

		var oDs = new YAHOO.util.DataSource(data);
		oDs.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
		oDs.responseSchema = { fields: ["competence", "score_got", "gap_negative", "gap_positive", "gap_percent_negative", "gap_percent_positive"] };
		//oTable.getDataSource();
/*
		var seriesDef = [
			{
				xField: "score_got",
				displayName: "<?php echo Lang::t('_SCORE', 'competences'); ?>",
				style: {
					fillColor: 0x0000FF
				}
			},
			{
				xField: "gap_negative",
				displayName: "<?php echo Lang::t('_GAP', 'fncroles'); ?>",
				style: {
					fillColor: 0xFF0000
				}
			},
			{
				xField: "gap_positive",
				displayName: "<?php echo Lang::t('_GAP', 'fncroles'); ?>",
				style: {
					fillColor: 0x00FF00
				}
			}
		];

		//used to format x axis
		var formatLabel = function( value ) {
			return value//YAHOO.util.Number.format(Number(value), {prefix: "$", thousandsSeparator: ","});
		}

		//Numeric Axis
		var scoreAxis = new YAHOO.widget.NumericAxis();
		scoreAxis.stackingEnabled = true;
		scoreAxis.labelFunction = formatLabel;

		this.oChartLinear = new YAHOO.widget.StackedBarChart("gap_chart", oDs, {
			series: seriesDef,
			yField: "competence",
			xAxis: scoreAxis
		});
*/
		var seriesDef = [
			{
				xField: "gap_percent_negative",
				displayName: "<?php echo Lang::t('_GAP', 'competences'); ?>",
				style: {
					fillColor: 0xCC0000,
					borderColor: 0xCC0000
				}
			},
			{
				xField: "gap_percent_positive",
				displayName: "<?php echo Lang::t('_GAP', 'competences'); ?>",
				style: {
					fillColor: 0x243356,
					borderColor: 0x243356
				}
			}
		];

		//used to format x axis
		var formatLabel = function( value ) {
			return value + " %";//YAHOO.util.Number.format(Number(value), {prefix: "$", thousandsSeparator: ","});
		}

		//Numeric Axis
		var scoreAxis = new YAHOO.widget.NumericAxis();
		scoreAxis.stackingEnabled = true;
		scoreAxis.labelFunction = formatLabel;

		this.oChartPercent = new YAHOO.widget.StackedBarChart("gap_chart", oDs, {
			series: seriesDef,
			yField: "competence",
			xAxis: scoreAxis,
			style: {
				xAxis:{
					zeroGridLine: {
						size:2,
						color:0x000000
					}
				}
			}
		});
	}
};

YAHOO.util.Event.onDOMReady(function(e) {
    UserGapAnalisys.sort = 'competence';
    UserGapAnalisys.dir = 'ASC';
	UserGapAnalisys.initChart();
});
</script>
<div class="std_block">
<?php echo getBackUi('index.php?r=adm/functionalroles/'.($from_gap ? 'man_users' : 'gap_analisys').'&id='.$id_fncrole, Lang::t('_BACK', 'standard')); ?>
	<div class="align-center">
		<div style="width:90%; margin-left:auto;margin-right:auto;">
			<div id="gap_chart" style="width:100%; height:<?php echo "".(count($chart_data)*30 + 40); ?>px;"></div>
		</div>
	</div>
	<br />
<?php

$icon_history = '<span class="ico-sprite subs_elem"><span>'.Lang::t('_HISTORY', 'standard').'</span></span>';
//$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_UNASSIGN', 'competences').'</span></span>';

$columns = array();
$columns[] = array('key' => 'competence', 'label' => Lang::t('_COMPETENCE', 'competences'), 'sortable' => true);
$columns[] = array('key' => 'score_got', 'label' => Lang::t('_SCORE', 'competences'), 'sortable' => true, 'formatter'=>'UserGapAnalisys.gotFormatter', 'className' => 'img-cell');
$columns[] = array('key' => 'score_req', 'label' => Lang::t('_REQUIRED_SCORE', 'competences'), 'sortable' => true, 'formatter'=>'UserGapAnalisys.reqFormatter', 'className' => 'img-cell');
$columns[] = array('key' => 'gap', 'label' => Lang::t('_GAP', 'fncroles'), 'sortable' => true, 'formatter'=>'UserGapAnalisys.gapFormatter', 'className' => 'img-cell');
$columns[] = array('key' => 'last_assign_date', 'label' => Lang::t('_DATE_OBTAINED', 'competences'), 'sortable' => true, 'className' => 'img-cell');
$columns[] = array('key' => 'date_expire', 'label' => Lang::t('_EXPIRATION_DATE', 'competences')/*, 'sortable' => true*/, 'formatter'=>'UserGapAnalisys.expireFormatter', 'className' => 'img-cell');

$rel_actions = '<a class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_CSV', 'report').'" '
	.'href="javascript: UserGapAnalisys.exportCSV(this);">'
	.'<span>'.Lang::t('_EXPORT_CSV', 'report').'</span></a>'
    .'<a class="ico-wt-sprite subs_xls" title="'.Lang::t('_EXPORT_XLS', 'report').'" '
	.'href="javascript: UserGapAnalisys.exportXLS(this);">'
	.'<span>'.Lang::t('_EXPORT_XLS', 'report').'</span></a>'
/*$rel_actions = '<a class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_CSV', 'report').'" '
	.'href="index.php?r=adm/functionalroles/export_gap&id_fncrole='.(int)$id_fncrole.'&format=csv">'
	.'<span>'.Lang::t('_EXPORT_CSV', 'report').'</span></a>'
	.'<a class="ico-wt-sprite subs_xls" title="'.Lang::t('_EXPORT_XLS', 'report').'" '
	.'href="index.php?r=adm/functionalroles/export_gap&id_fncrole='.(int)$id_fncrole.'&format=xls">'
	.'<span>'.Lang::t('_EXPORT_XLS', 'report').'</span></a>'*/;

$this->widget('table', array(
	'id'			=> 'fncroles_usergap_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/functionalroles/getusergaptabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'competence',
	'dir'			=> 'asc',
	'generateRequest' => 'UserGapAnalisys.requestBuilder',
	'columns'		=> $columns,
	'rel_actions' => $rel_actions,    
	'fields'		=> array('last_assign_date', 'date_expire', 'score_req', 'score_got', 'gap', 'competence', 'id_competence', 'is_expired', 'type')
));

?>
<?php echo getBackUi('index.php?r=adm/functionalroles/'.($from_gap ? 'man_users' : 'gap_analisys').'&id='.$id_fncrole, Lang::t('_BACK', 'standard')); ?>
</div>