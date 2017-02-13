



<script type="text/javascript">
var LogManagement = {
	filterText: "<?php echo $filter_text; ?>",
	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};
		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
		return  "&results=" 	+ results +
				"&startIndex=" 	+ startIndex +
				"&sort="		+ sort +
				"&dir="			+ dir +
				"&filter=" + LogManagement.filterText;
	}
}


YAHOO.util.Event.onDOMReady(function(e) {
	YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
		switch (YAHOO.util.Event.getCharCode(e)) {
			case 13: {
				YAHOO.util.Event.preventDefault(e);
				LogManagement.filterText = this.value;
				DataTable_logtable.refresh();
			} break;
		}
	});

	YAHOO.util.Event.addListener("filter_set", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		LogManagement.filterText = YAHOO.util.Dom.get("filter_text").value;
		DataTable_logtable.refresh();
	});

	YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Dom.get("filter_text").value = "";
		LogManagement.filterText = "";
		DataTable_logtable.refresh();
	});
});
</script>
<?php echo getTitleArea(Lang::t('_LIST_DB_UPGRADES', 'configuration')); ?>
<div class="std_block">

<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="deletedusers_simple_filter_options">
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
	</div>
</div>
<?php

$params = array(
	'id' => 'logtable',
	'ajaxUrl' => 'ajax.adm_server.php?r='. $this->link.'/getdbupgradestabledata',
	'rowsPerPage' => Get::sett('visuItem', 25),
	'startIndex' => 0,
	'results' => Get::sett('visuItem', 25),
	'sort' => 'execution_date',
	'dir' => 'desc',
	'columns' => array(
			array('key' => 'script_id', 'label' => Lang::t('_SCRIPT_ID', 'standard'), 'sortable' => true),
			array('key' => 'script_name', 'label' => Lang::t('_SCRIPT_NAME', 'standard'), 'sortable' => true),		
			array('key' => 'script_description', 'label' => Lang::t('_SCRIPT_DESCRIPTION', 'standard'), 'sortable' => true),
			array('key' => 'script_version', 'label' => Lang::t('_SCRIPT_VERSION', 'standard'), 'sortable' => true),
			array('key' => 'core_version', 'label' => Lang::t('_CORE_VERSION', 'standard'), 'sortable' => true),
			array('key' => 'creation_date', 'label' => Lang::t('_CREATION_DATE', 'standard'), 'sortable' => true),
			array('key' => 'execution_date', 'label' => Lang::t('_EXECUTION_DATE', 'standard'), 'sortable' => true)
		),
	'fields' => array('script_id', 'script_name', 'script_description', 'script_version', 'core_version', 'creation_date', 'execution_date'),
	'generateRequest' => 'LogManagement.requestBuilder'
);

$this->widget('table', $params);

?>
<?php echo getBackUi('index.php?r='. $this->link.'/show', Lang::t('_BACK', 'standard')); ?>
</div>