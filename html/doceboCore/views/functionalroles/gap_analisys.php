<?php
echo getTitleArea($title_arr);
?>
<script type="text/javascript">
var GapAnalisys = {

	idFncrole: <?php echo (int)$id_fncrole; ?>,
	filterText: "<?php echo $filter_text; ?>",
	showGap: <?php echo $advanced_filter['gap_filter']; ?>, //0 = all, 1 = only gaps, 2 = only non-gap
	showExpired: <?php echo $advanced_filter['expire_filter']; ?>, //0 = all, 1 = only expired, 2 = only active
	showCompetences: [],

	oLangs: new LanguageManager({
		_GAP_ANALYSIS: "<?php echo Lang::t('_GAP_ANALYSIS', 'fncroles'); ?>"
	}),

	gapFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = oData <= 0
			? '<b class=green>'+Math.abs(oData)+'</b>'//'<span class="ico-sprite subs_actv"><span><?php ?></span></span>'
			: '<b class=red>- '+oData+'</b>';
	},

	expireFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = (oRecord.getData("is_expired")
			? '<span class="ico-sprite fd_notice"><span><?php ?></span></span>&nbsp;<b class="red">'+oData+'</b>'
			: oData);
	},

	userGapAnalisysFormatter: function(elLiner, oRecord, oColumn, oData) {
		var url = 'index.php?r=adm/functionalroles/user_gapanalisys'
			+'&id_fncrole='+GapAnalisys.idFncrole+'&id_user='+oRecord.getData("idst");
		elLiner.innerHTML = '<a class="ico-sprite subs_chart" href="'+url+'" '
			+'title="'+GapAnalisys.oLangs.get('_GAP_ANALYSIS')+'">'
			+'<span>'+GapAnalisys.oLangs.get('_GAP_ANALYSIS')+'</span></a>';
	},

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};

		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";

		var G = GapAnalisys;
		return "&results=" + results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir+
				"&id_fncrole=" + GapAnalisys.idFncrole +
				"&filter_text=" + GapAnalisys.filterText +
				"&gap=" + G.showGap +
				"&expired=" + G.showExpired +
				(G.showCompetences.length > 0 ? "&competences="+G.showCompetences.join(",") : "");
	}
};

YAHOO.util.Event.onDOMReady(function() {
	var E = YAHOO.util.Event, D = YAHOO.util.Dom, G = GapAnalisys;

	D.get("show_gap_"+G.showGap).checked = true;
	//D.get("show_expire_"+G.showExpired).checked = true;


	YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
		switch (YAHOO.util.Event.getCharCode(e)) {
			case 13: {
				YAHOO.util.Event.preventDefault(e);
				G.filterText = this.value;
				DataTable_fncroles_gap_table.refresh();
			} break;
		}
	});

	E.addListener("filter_set", "click", function(e) {
		E.preventDefault(e);
		G.filterText = D.get("filter_text").value;
		DataTable_fncroles_gap_table.refresh();
	});

	E.addListener("filter_reset", "click", function(e) {
		E.preventDefault(e);
		D.get("filter_text").value = "";
		G.filterText = "";
		DataTable_fncroles_gap_table.refresh();
	});

	E.addListener("advanced_search", "click", function(e){
		var el = D.get("advanced_search_options");
		if (el.style.display != 'block') {
			el.style.display = 'block'
		} else {
			el.style.display = 'none'
		}
	});

	E.addListener("set_advanced_filter-button", "click", function(e) {
		var i, el1, el2;
		for (i=0; i<3; i++) {
			el1 = D.get("show_gap_"+i);
			//el2 = D.get("show_expire_"+i);
			if (el1.checked) G.showGap = el1.value;
			//if (el2.checked)	G.showExpired = el2.value;
		}
		DataTable_fncroles_gap_table.refresh();
	});

	E.addListener("reset_advanced_filter-button", "click", function(e) {
		D.get("show_gap_0").checked = true;
		//D.get("show_expire_0").checked = true;
		G.showGap = 0;
		G.showExpired = 0;
		DataTable_fncroles_gap_table.refresh();
	});

});
</script>
<div class="std_block">

<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="competences_simple_filter_options" style="display: block;">
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
		<a id="advanced_search" class="advanced_search" href="javascript:;"><?php echo Lang::t("_ADVANCED_SEARCH", 'standard'); ?></a>
		<div id="advanced_search_options" class="advanced_search_options" <?php
			if ($advanced_filter['active'])
				echo 'style="display: block;"';
			else
				echo 'style="display: none;"';
			?>>
			<?php
				$show_gap_values = array(
					0 => Lang::t('_ALL', 'standard'),
					1 => Lang::t('_GAP_ONLY', 'fncroles'),
					2 => Lang::t('_NO_GAP_ONLY', 'fncroles')
				);
				/*$show_expire_values = array(
					0 => Lang::t('_ALL', 'standard'),
					1 => Lang::t('_EXPIRED_ONLY', 'standard'),
					2 => Lang::t('_NOT_EXPIRED_ONLY', 'standard')
				);*/
				echo Form::getRadioHoriz(Lang::t('_FILTER', 'fncroles'), 'show_gap', 'show_gap', array_flip($show_gap_values), $advanced_filter['gap_filter']);
				//echo Form::getRadioHoriz(Lang::t('_EXPIRE_FILTER', 'fncroles'), 'show_expire', 'show_expire', array_flip($show_expire_values), $advanced_filter['expire_filter']);

				echo Form::openButtonSpace();
				echo Form::getButton('set_advanced_filter', false, Lang::t('_SEARCH', 'standard'));
				echo Form::getButton('reset_advanced_filter', false, Lang::t('_UNDO', 'standard'));
				echo Form::closeButtonSpace();
			?>
		</div>
	</div>
</div>

<?php

$icon_history = '<span class="ico-sprite subs_elem"><span>'.Lang::t('_HISTORY', 'standard').'</span></span>';
//$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_UNASSIGN', 'competences').'</span></span>';
$icon_chart = '<span class="ico-sprite subs_chart"><span>'.Lang::t('_GAP_ANALYSIS', 'fncroles').'</span></span>';

$columns = array();
$columns[] = array('key' => 'competence', 'label' => Lang::t('_COMPETENCE', 'competences'), 'sortable' => true);
$columns[] = array('key' => 'userid', 'label' => Lang::t('_USER', 'standard'), 'sortable' => true);
$columns[] = array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'sortable' => true);
$columns[] = array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'sortable' => true);
$columns[] = array('key' => 'score_got', 'label' => Lang::t('_SCORE', 'competences'), 'sortable' => true, 'className' => 'img-cell');
$columns[] = array('key' => 'score_req', 'label' => Lang::t('_REQUIRED_SCORE', 'competences'), 'sortable' => true, 'className' => 'img-cell');
$columns[] = array('key' => 'gap', 'label' => Lang::t('_GAP', 'fncroles'), 'sortable' => true, 'formatter'=>'GapAnalisys.gapFormatter', 'className' => 'img-cell');
$columns[] = array('key' => 'last_assign_date', 'label' => Lang::t('_DATE_OBTAINED', 'competences'), 'sortable' => true, 'className' => 'img-cell');
$columns[] = array('key' => 'date_expire', 'label' => Lang::t('_EXPIRATION_DATE', 'competences')/*, 'sortable' => true*/, 'formatter'=>'GapAnalisys.expireFormatter', 'className' => 'img-cell');
$columns[] = array('key' => 'gap_user', 'label' => $icon_chart, 'formatter' => 'GapAnalisys.userGapAnalisysFormatter', 'className' => 'img-cell');

$rel_actions = '<a class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_CSV', 'report').'" '
	.'href="index.php?r=adm/functionalroles/export_gap&id_fncrole='.(int)$id_fncrole.'&format=csv">'
	.'<span>'.Lang::t('_EXPORT_CSV', 'report').'</span></a>'
	/*.'<a class="ico-wt-sprite subs_xls" title="'.Lang::t('_EXPORT_XLS', 'report').'" '
	.'href="index.php?r=adm/functionalroles/export_gap&id_fncrole='.(int)$id_fncrole.'&format=xls">'
	.'<span>'.Lang::t('_EXPORT_XLS', 'report').'</span></a>'*/;

$this->widget('table', array(
	'id'			=> 'fncroles_gap_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/functionalroles/getgaptabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'competence',
	'dir'			=> 'asc',
	'generateRequest' => 'GapAnalisys.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('idst', 'userid', 'firstname', 'lastname', 'last_assign_date', 'date_expire', 'score_req', 'score_got', 'gap', 'competence', 'id_competence', 'is_expired'),
	'rel_actions' => $rel_actions
));

?>
</div>