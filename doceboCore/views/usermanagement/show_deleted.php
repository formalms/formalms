<script type="text/javascript">
var DeletedManagement = {
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
				"&filter=" + DeletedManagement.filterText;
	}
}


YAHOO.util.Event.onDOMReady(function(e) {
	YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
		switch (YAHOO.util.Event.getCharCode(e)) {
			case 13: {
				YAHOO.util.Event.preventDefault(e);
				DeletedManagement.filterText = this.value;
				DataTable_deletedtable.refresh();
			} break;
		}
	});

	YAHOO.util.Event.addListener("filter_set", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		DeletedManagement.filterText = YAHOO.util.Dom.get("filter_text").value;
		DataTable_deletedtable.refresh();
	});

	YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Dom.get("filter_text").value = "";
		DeletedManagement.filterText = "";
		DataTable_deletedtable.refresh();
	});
});
</script>
<?php
echo getTitleArea(array(
	'index.php?r='. $this->link.'/show' => Lang::t('_ORGCHART', 'directory'),
	Lang::t('_DELETED_USERS', 'admin_directory')
));
?>
<div class="std_block">
<?php echo getBackUi('index.php?r='. $this->link.'/show', Lang::t('_BACK', 'standard')); ?>
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
	'id' => 'deletedtable',
	'ajaxUrl' => 'ajax.adm_server.php?r='. $this->link.'/getdeleteduserstabledata',
	'rowsPerPage' => Get::sett('visuItem', 25),
	'startIndex' => 0,
	'results' => Get::sett('visuItem', 25),
	'sort' => 'userid',
	'dir' => 'desc',
	'columns' => array(
			array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'sortable' => true),
			array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'sortable' => true),
			array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'sortable' => true),
			array('key' => 'email', 'label' => Lang::t('_EMAIL', 'standard'), 'sortable' => true),
			array('key' => 'deletion_date', 'label' => Lang::t('_DELETION_DATE', 'admin_directory'), 'sortable' => true),
			array('key' => 'deleted_by', 'label' => Lang::t('_DELETED_BY', 'admin_directory'), 'sortable' => true)
		),
	'fields' => array('id', 'userid', 'firstname', 'lastname', 'email', 'deletion_date', 'deleted_by'),
	'generateRequest' => 'DeletedManagement.requestBuilder'
);

$this->widget('table', $params);

?>
<?php echo getBackUi('index.php?r='. $this->link.'/show', Lang::t('_BACK', 'standard')); ?>
</div>