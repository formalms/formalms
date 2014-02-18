<?php
echo getTitleArea(array(
	'index.php?r=alms/kb/show' => Lang::t('_CONTENT_LIBRARY', 'kb'),
	Lang::t('_ADD', 'kb')
));
?>
<div class="std_block">

<div>
<label for="type_sel"><?php echo Lang::t('_TYPE', 'kb'); ?>:</label>
<select id="type_sel" name="type_sel">
	<option value="0"><?php echo Lang::t('_SELECT', 'kb'); ?>..</option>
	<option value="scoitem"><?php echo Lang::t('_RES_SCORM', 'kb'); ?></option>
	<option value="file"><?php echo Lang::t('_RES_FILE', 'kb'); ?></option>
	<option value="htmlpage"><?php echo Lang::t('_RES_HTMLPAGE', 'kb'); ?></option>
	<option value="link"><?php echo Lang::t('_RES_LINK', 'kb'); ?></option>
	<option value="glossary"><?php echo Lang::t('_RES_GLOSSARY', 'kb'); ?></option>
</select>
</div>

<script type="text/javascript">
YAHOO.namespace("KbManagement");

var KbManagement = {
	filterText :"",
	res_type :"<?php $type; ?>",

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;

		oState = oState || {pagination: null, sortedBy: null};

		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";

		return  "&results="	+ results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir+
				"&type=" + KbManagement.res_type+
				"&filter_text="+KbManagement.filterText;
	}
}


function frm_categorize(elLiner, oRecord, oColumn, oData) {
  elLiner.innerHTML = '<a id="frm_edit_'+oRecord.getData("r_item_id")+'" class="ico-sprite subs_categorize" '
    +' href="index.php?r=alms/kb/categorize&amp;id='+oRecord.getData("r_item_id")
		+'&amp;type='+KbManagement.res_type+'&amp;env='+oRecord.getData("env")
		+'&amp;title='+oRecord.getData("title")+'">'
    +'<span><?php echo Lang::t('_CATEGORIZE', 'kb'); ?></span></a>'	;
}


YAHOO.util.Event.onDOMReady(function(e) {

	YAHOO.util.Event.addListener("filter_set", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		KbManagement.filterText = YAHOO.util.Dom.get("filter_text").value;
		DataTable_kb_table.refresh();
	});

	YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Dom.get("filter_text").value = "";
		KbManagement.filterText = "";
		DataTable_kb_table.refresh();
	});
	
	YAHOO.util.Event.addListener("type_sel", "change", function(e) {
		YAHOO.util.Event.preventDefault(e);
		KbManagement.res_type = YAHOO.util.Dom.get("type_sel").value;
		DataTable_kb_table.refresh();
	});

});

	function svSwitch(a_elem) {
		sUrl =a_elem.href;

		var callback = {
			success: function(o) {
				DataTable_kb_table.refresh();
			}
			/*failure: .. */
		};

		YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
	};

</script>


<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="usermanagement_simple_filter_options" style="display: block;">
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
	</div>
</div>




<?php

$my_cols_def =array(
		array('key' => 'title', 'label' => Lang::t('_TITLE', 'kb'), 'sortable' => true),
		array('key' => 'r_type', 'label' => Lang::t('_TYPE', 'kb'), 'sortable' => true),
		array('key' => 'env', 'label' => Lang::t('_ENVIRONMENT', 'kb'), 'sortable' => true),
		array('key' => 'edit', 'label' => '<span class="ico-sprite subs_categorize"><span>'.Lang::t('_CATEGORIZE', 'kb').'</span></span>', 'formatter'=>'frm_categorize', 'className' => 'img-cell')
);

if ($type == 'scoitem') {
	array_unshift($my_cols_def, array(
		'key' => 'scorm_title', 'label' => Lang::t('_CHAPTER_TITLE', 'kb'), 'sortable' => true
	));
}

$this->widget('table', array(
	'id'			=> 'kb_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/kb/getuncategorized',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'title',
	'dir'			=> 'asc',
	'generateRequest' => 'KbManagement.requestBuilder',
	'columns'		=> $my_cols_def,
	'fields'		=> array('scorm_title', 'title', 'r_type', 'env', 'edit', 'r_item_id'),
));
?>


</div><!--- std_block --->