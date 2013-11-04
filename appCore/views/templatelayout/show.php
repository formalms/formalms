<?php
echo //Layout::bc(array(
	Lang::t('_TEMPLATE_LAYOUT', 'template')
//))
;
?>
<script type="text/javascript">
	var TemplateLayout = {
		filterText: "<?php $filter_text ?>",
		requestBuilder: function(oState, oSelf) {
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
				"&filter=" + TemplateLayout.filterText;
		},
		modifyFormatter: function(elLiner, oRecord, oColumn, oData) {
			elLiner.innerHTML = '<a href="index.php?r=adm/templatelayout/edit&id='+oRecord.getData("id")+'" class="ico-sprite subs_mod"><span><?php echo Lang::t('_MOD'); ?></span><a/>';
		}
	};

	YAHOO.util.Event.onDOMReady(function(e) {
		var refreshTable = function() {
			var oDt = DataTable_templatelayout;
			var oState = oDt.getState();
			var request = oDt.get("generateRequest")(oState, oDt);
			var oCallback = {
				success : oDt.onDataReturnSetRows,
				failure : oDt.onDataReturnSetRows,
				argument : oState,
				scope : oDt
			};
			oDt.getDataSource().sendRequest(request, oCallback);
		};

		YAHOO.util.Event.addListener("filter_set", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			TemplateLayout.filterText = YAHOO.util.Dom.get("filter_text").value;
			refreshTable();
		});
	});
</script>
<div class="std_block">
	<div class="quick_search_form">
		<div>
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
			?>
		</div>
	</div>
	<?php
	$this->widget('table', array(
		'id' => 'templatelayout',
		'ajaxUrl' => 'ajax.adm_server.php?r=adm/templatelayout/tabledata',
		'columns' => array(
			array('key' => 'name', 'label' => Lang::t('_TEMPLATE_NAME', 'template'), 'sortable' => true),
			array('key' => 'last_modify', 'label' => Lang::t('_LAST_MODIFY', 'template'), 'sortable' => true),
			array('key' => 'mod', 'label' => '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'standard').'</span></span>', 'formatter' => 'TemplateLayout.modifyFormatter'),
			array('key' => 'del', 'label' => '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>', 'formatter' => '"stdDelete"')
		),
		'sort' => $sort,
		'dir' => $dir,
		'fields' => array('id', 'name', 'date_creation', 'last_modify', 'del'),
		'rel_actions' => '<a class="ico-wt-sprite subs_add" href="index.php?r=adm/templatelayout/create"><span>'.Lang::t('_ADD').'</span></a>',
		'generateRequest' => 'TemplateLayout.requestBuilder',
	));
	?>
</div>