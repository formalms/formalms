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
    templatePath: "<?php echo Get::tmpl_path(); ?>",            
	dynSelection: {},
	fieldList: <?php echo $fieldlist_js; ?>,
	numVarFields: <?php echo $num_var_fields; ?>,
    sort: null,
    dir: null,

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

        GapAnalisys.sort = sort;
        GapAnalisys.dir = dir;
            
		var G = GapAnalisys;
		var output = "&results=" + results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir+
				"&id_fncrole=" + G.idFncrole +
				"&filter_text=" + G.filterText +
				"&gap=" + G.showGap +
				"&expired=" + G.showExpired +
                "&dyn_filter= true" +
				(G.showCompetences.length > 0 ? "&competences="+G.showCompetences.join(",") : "");
                for (i=0; i<G.numVarFields; i++) {
                    output += "&_dyn_field["+i+"]=" + YAHOO.util.Dom.get("_dyn_field_selector_"+i).value
                }
        return output;
	},
	beforeRenderEvent: function() {
        var slist = YAHOO.util.Selector.query('select[id^=_dyn_field_selector_]');
		var blist = YAHOO.util.Selector.query('a[id^=_dyn_field_sort_]');
		var i;

		for (i=0; i<slist.length; i++) {
			slist[i].disabled = true;
			YAHOO.util.Event.purgeElement(slist[i]);
		}
		for (i=0; i<blist.length; i++) {
			YAHOO.util.Event.purgeElement(blist[i]);
		}
    },

	postRenderEvent: function() {
        var slist = YAHOO.util.Selector.query('select[id^=_dyn_field_selector_]');
		var blist = YAHOO.util.Selector.query('a[id^=_dyn_field_sort_]');
		var i;

		for (i=0; i<slist.length; i++) {
			slist[i].disabled = false;
			GapAnalisys.setDropDownRefreshEvent.call(slist[i]);
		}
		for (i=0; i<blist.length; i++) {
			GapAnalisys.setSortButtonRefreshEvent.call(blist[i]);
		}
	},            
    setDropDownRefreshEvent: function() {
            YAHOO.util.Event.addListener(this, "change", function() {
                GapAnalisys.dynSelection[this.id] = this.selectedIndex;
                DataTable_fncroles_gap_table.refresh();
            });
    },
	setSortButtonRefreshEvent: function() {
		var oDt = DataTable_fncroles_gap_table;
		YAHOO.util.Event.addListener(this, "click", function(e) {
			YAHOO.util.Event.preventDefault(e);

			var oColumn = oDt.getColumn(this);

			//load adjusted <select> into column label
			var index = this.id.replace('_dyn_field_sort_', '');
			var selected = YAHOO.util.Dom.get('_dyn_field_selector_'+index).value;
			oColumn.label = GapAnalisys.getDynLabelMarkup(index, selected);

			var oSortedBy = oDt.get("sortedBy"), sDir = oDt.CLASS_ASC;
			if (oSortedBy.key == oColumn.getKey()) {
				sDir = (oSortedBy.dir == oDt.CLASS_ASC ? oDt.CLASS_DESC : oDt.CLASS_ASC);
			}

			oDt.sortColumn(oColumn, sDir);
		});
	},
	getDynLabelMarkup: function(index, selected) {
		var x, id = '_dyn_field_selector_'+index, sort_str = GapAnalisys.oLangs.get('_SORT');
		var output = '<select id="'+id+'" name="_dyn_field_selector['+index+']">';
		for (x in GapAnalisys.fieldList) {
			output += '<option value="'+x+'"'
			+( selected == x ? ' selected="selected"' : '' )
			+'>'+GapAnalisys.fieldList[x]+'</option>';
		}
		output += '</select>';

		output += '<a id="_dyn_field_sort_'+index+'" href="javascript:;">';
		output += '<img src="'+GapAnalisys.templatePath+'images/standard/sort.png" ';
		output += 'title="'+sort_str+'" alt="'+sort_str+'" />';
		output += '</a>';

		GapAnalisys.dynSelection[id] = selected;
		return output;
	},
    exportCSV: function(e) {
        YAHOO.util.Event.preventDefault(e);
        var dyn_field = '';
        for (i=0; i<GapAnalisys.numVarFields; i++) {
            dyn_field += "&_dyn_field["+i+"]=" + YAHOO.util.Dom.get("_dyn_field_selector_"+i).value
        }
        dyn_field += "&sort=" + GapAnalisys.sort;
        dyn_field += "&dir=" + GapAnalisys.dir;
        window.open("index.php?r=adm/functionalroles/export_gap&id_fncrole="+this.idFncrole+"&format=csv&dyn_filter= true"+dyn_field);
    },
    exportXLS: function(e) {
        YAHOO.util.Event.preventDefault(e);
        var dyn_field = '';
        for (i=0; i<GapAnalisys.numVarFields; i++) {
            dyn_field += "&_dyn_field["+i+"]=" + YAHOO.util.Dom.get("_dyn_field_selector_"+i).value
        }
        dyn_field += "&sort=" + GapAnalisys.sort;
        dyn_field += "&dir=" + GapAnalisys.dir;
        window.open("index.php?r=adm/functionalroles/export_gap&id_fncrole="+this.idFncrole+"&format=xls&dyn_filter= true"+dyn_field);
    }
};

YAHOO.util.Event.onDOMReady(function() {
	var E = YAHOO.util.Event, D = YAHOO.util.Dom, G = GapAnalisys;

	D.get("show_gap_"+G.showGap).checked = true;
	//D.get("show_expire_"+G.showExpired).checked = true;


	E.addListener('filter_text', "keypress", function(e) {
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

$dyn_labels = array();
$dyn_filter = array();

for ($i=0; $i<$num_var_fields; $i++) {
	$label = '<select id="_dyn_field_selector_'.$i.'" name="_dyn_field_selector['.$i.']">';
	foreach ($fieldlist as $key => $value) {
		 $label .= '<option value="'.$key.'"'
			.( $selected[$i] == $key ? ' selected="selected"' : '' )
			.'>'.$value.'</option>';
	}
	$label .= '</select>';
	$label .= '<a id="_dyn_field_sort_'.$i.'" href="javascript:;">';
	$label .= '<img src="'.Get::tmpl_path().'images/standard/sort.png" title="'.Lang::t('_SORT', 'standard').'" alt="'.Lang::t('_SORT', 'standard').'" />';
	$label .= '</a>';
	$dyn_filter[$i] = $selected[$i];
	$dyn_labels[$i] = $label;
}

$columns = array();
$columns[] = array('key' => 'competence', 'label' => Lang::t('_COMPETENCE', 'competences'), 'sortable' => true);
$columns[] = array('key' => 'userid', 'label' => Lang::t('_USER', 'standard'), 'sortable' => true);
$columns[] = array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'sortable' => true);
$columns[] = array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'sortable' => true);
for ($i=0; $i<$num_var_fields; $i++) {
	$columns[] = array('key' => '_dyn_field_'.$i, 'label' => $dyn_labels[$i]);
}
$columns[] = array('key' => 'score_got', 'label' => Lang::t('_SCORE', 'competences'), 'sortable' => true, 'className' => 'img-cell');
$columns[] = array('key' => 'score_req', 'label' => Lang::t('_REQUIRED_SCORE', 'competences'), 'sortable' => true, 'className' => 'img-cell');
$columns[] = array('key' => 'gap', 'label' => Lang::t('_GAP', 'fncroles'), 'sortable' => true, 'formatter'=>'GapAnalisys.gapFormatter', 'className' => 'img-cell');
$columns[] = array('key' => 'last_assign_date', 'label' => Lang::t('_DATE_OBTAINED', 'competences'), 'sortable' => true, 'className' => 'img-cell');
$columns[] = array('key' => 'date_expire', 'label' => Lang::t('_EXPIRATION_DATE', 'competences')/*, 'sortable' => true*/, 'formatter'=>'GapAnalisys.expireFormatter', 'className' => 'img-cell');
$columns[] = array('key' => 'gap_user', 'label' => $icon_chart, 'formatter' => 'GapAnalisys.userGapAnalisysFormatter', 'className' => 'img-cell');

$rel_actions = '<a class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_CSV', 'report').'" '
	.'href="javascript: GapAnalisys.exportCSV(this);">'
	.'<span>'.Lang::t('_EXPORT_CSV', 'report').'</span></a>'
    .'<a class="ico-wt-sprite subs_xls" title="'.Lang::t('_EXPORT_XLS', 'report').'" '
	.'href="javascript: GapAnalisys.exportXLS(this);">'
	.'<span>'.Lang::t('_EXPORT_XLS', 'report').'</span></a>'
	/*.'<a class="ico-wt-sprite subs_xls" title="'.Lang::t('_EXPORT_XLS', 'report').'" '
	.'href="index.php?r=adm/functionalroles/export_gap&id_fncrole='.(int)$id_fncrole.'&format=xls">'
	.'<span>'.Lang::t('_EXPORT_XLS', 'report').'</span></a>'*/;

$arr_fields = array('idst', 'userid', 'firstname', 'lastname', 'last_assign_date', 'date_expire', 'score_req', 'score_got', 'gap', 'competence', 'id_competence', 'is_expired');
for ($i=0; $i<$num_var_fields; $i++) {
	$arr_fields[] = '_dyn_field_'.$i;
}

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
	'fields'		=> $arr_fields,
	'rel_actions' => $rel_actions,
    'events' => array(
		'beforeRenderEvent' => 'GapAnalisys.beforeRenderEvent',
		'postRenderEvent' => 'GapAnalisys.postRenderEvent'
	)    
));

?>
</div>