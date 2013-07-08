<?php

echo getTitleArea(array(
	'index.php?r=adm/adminmanager/show' => Lang::t('_ADMIN_MANAGER', 'menu'),
	Lang::t('_LOCATION', 'adminmanager').' : '.$model->getAdminFullname($id_user)
));

?>
<div class="std_block">
<?php

echo getBackUi('index.php?r=adm/adminmanager/show', Lang::t('_BACK', 'standard'));

echo Form::openForm('classlocations_selection_form', 'index.php?r=adm/adminmanager/classlocations_set');
echo Form::getHidden('selection', 'selection', implode(",", $selection));
echo Form::getHidden('id_user', 'id_user', $id_user);

//--- SEARCH FILTER -------

$this->widget('tablefilter', array(
	'id' => 'classlocations_filter',
	'filter_text' => isset($filter_text) ? $filter_text : "",
	'js_callback_set' => 'ClassLocations.setFilter',
	'js_callback_reset' => 'ClassLocations.resetFilter'
));


//--- TABLE -------

$rel_action_over = '<span class="ma_selected_users">'
		.'<b id="num_users_selected_top">'.(int)(isset($num_selected) ? $num_selected : '0').'</b> '.Lang::t('_SELECTED', 'admin_directory')
		.'</span>';
$rel_action_bottom = '<span class="ma_selected_users">'
		.'<b id="num_users_selected_bottom">'.(int)(isset($num_selected) ? $num_selected : '0').'</b> '.Lang::t('_SELECTED', 'admin_directory')
		.'</span>';

$_params = array(
	'id'			=> 'classlocations_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/adminmanager/getclasslocationstabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'location',
	'dir'			=> 'asc',
	'generateRequest' => 'ClassLocations.requestBuilder',
	'columns'		=> array(
		array('key' => 'location', 'label' => Lang::t('_LOCATION', 'lms'), 'sortable' => true, 'formatter' => 'ClassLocations.labelFormatter')
	),
	'fields'		=> array('id', 'location'),
	'stdSelection' => true,
	'initialSelection' => $selection,
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'events' => array(
		'initEvent' => 'ClassLocations.initEvent'
	)
);

$this->widget('table', $_params);

echo Form::openButtonSpace();
echo Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'));
echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'));
echo Form::closeButtonSpace();

echo Form::closeForm();

echo getBackUi('index.php?r=adm/adminmanager/show', Lang::t('_BACK', 'standard'));

?>
</div>
<script type="text/javascript">
var ClassLocations = {

	filterText: "",

	init: function(oConfig) {
		this.filterText = oConfig.filterText;

		YAHOO.util.Event.addListener("classlocations_selection_form", "submit", function() {
			YAHOO.util.Dom.get("selection").value = DataTableSelector_classlocations_table.toString();
		});
	},

	setFilter: function() {
		ClassLocations.filterText = this.value;
		DataTable_classlocations_table.refresh();
	},

	resetFilter: function() {
		this.value = "";
		ClassLocations.filterText = "";
		DataTable_classlocations_table.refresh();
	},

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};
		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
		return "&results=" + results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir +
				"&filter_text=" + ClassLocations.filterText;
	},

	setNumUserSelected: function(num) {
		var prefix = "num_users_selected_", D = YAHOO.util.Dom;
		D.get(prefix+"top").innerHTML = num;
		D.get(prefix+"bottom").innerHTML = num;
	},

	initEvent: function() {
		var updateSelected = function() {
			ClassLocations.setNumUserSelected(this.num_selected);
		};
		var ds = DataTableSelector_classlocations_table;
		ds.subscribe("add", updateSelected);
		ds.subscribe("remove", updateSelected);
		ds.subscribe("reset", updateSelected);
	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="classlocations_table_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	}
};


ClassLocations.init({
	filterText: "<?php echo $filter_text; ?>"
});
</script>