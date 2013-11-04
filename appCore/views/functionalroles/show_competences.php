<?php echo getTitleArea($title_arr); ?>
<script type="text/javascript">
var Competences = {
	filterText: "",

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
				"&dir=" + dir+
				"&id_fncrole=" + <?php echo (int)$id_fncrole; ?> +
				"&filter_text=" + Competences.filterText;
	}
}

YAHOO.util.Event.onDOMReady(function() {
	YAHOO.util.Event.addListener("filter_set", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		Competences.filterText = YAHOO.util.Dom.get("filter_text").value;
		DataTable_competences_table.refresh();
	});

	YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Dom.get("filter_text").value = "";
		Competences.filterText = "";
		DataTable_competences_table.refresh();
	});
});
</script>
<div class="std_block">
<?php echo getBackUi('index.php?r=adm/functionalroles/show', Lang::t('_BACK', 'standard')); ?>
<?php if (isset($result_message)) echo $result_message; ?>
<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="competences_simple_filter_options" style="display: block;">
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
	</div>
</div>
<?php

$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>';

$columns = array(
	array('key' => 'category', 'label' => Lang::t('_CATEGORY', 'competences'), 'sortable' => true),
	array('key' => 'name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard'), 'sortable' => true),
	array('key' => 'typology', 'label' => Lang::t('_TYPOLOGY', 'competences'), 'className' => 'img-cell', 'sortable' => true),
	array('key' => 'type', 'label' => Lang::t('_TYPE', 'standard'), 'className' => 'img-cell', 'sortable' => true),
	array('key' => 'score', 'label' => Lang::t('_MIN_SCORE', 'competences'), 'sortable' => true, 'className' => 'img-cell'),
	array('key' => 'expiration', 'label' => Lang::t('_EXPIRATION_DAYS', 'competences'), 'sortable' => true, 'className' => 'img-cell'),
	array('key' => 'del', 'label' => $icon_del, 'formatter'=>'stdDelete', 'className' => 'img-cell')
);

$rel_action_over = '<a id="sel_competences_over" class="ico-wt-sprite subs_add" '
	.'href="index.php?r=adm/functionalroles/sel_competences&id_fncrole='.(int)$id_fncrole.'">'
	.'<span>'.Lang::t('_ASSIGN', 'fncroles').'</span></a>'
	.($count > 0 ? '<a id="man_competences_over" class="ico-wt-sprite subs_mod" '
	.'href="index.php?r=adm/functionalroles/man_competences_properties&id_fncrole='.(int)$id_fncrole.'">'
	.'<span>'.Lang::t('_PROPERTIES', 'fncroles').'</span></a>' : '');

$rel_action_bottom = '<a id="sel_competences_bottom" class="ico-wt-sprite subs_add" '
	.'href="index.php?r=adm/functionalroles/sel_competences&id_fncrole='.(int)$id_fncrole.'">'
	.'<span>'.Lang::t('_ASSIGN', 'fncroles').'</span></a>'
	.($count > 0 ? '<a id="man_competences_bottom" class="ico-wt-sprite subs_mod" '
	.'href="index.php?r=adm/functionalroles/man_competences_properties&id_fncrole='.(int)$id_fncrole.'">'
	.'<span>'.Lang::t('_PROPERTIES', 'fncroles').'</span></a>' : '');

$this->widget('table', array(
	'id'			=> 'competences_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/functionalroles/getcompetencetabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'name',
	'dir'			=> 'asc',
	'generateRequest' => 'Competences.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('id', 'category', 'name', 'description', 'typology', 'type', 'score', 'expiration', 'del'),
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'delDisplayField' => 'name'
));

?>
<?php echo getBackUi('index.php?r=adm/functionalroles/show', Lang::t('_BACK', 'standard')); ?>
</div>