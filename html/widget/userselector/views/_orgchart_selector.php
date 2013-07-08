<?php if ($use_form_input) { ?>
<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function(e) {
	
	var input = YAHOO.util.Dom.get("userselector_input_<?php echo $id; ?>");
	if (input) {
		YAHOO.util.Event.addListener(input.form, "submit", function(e) {
			var str = TreeView_orgchart_selector_tree_<?php echo $id; ?>.oSelector.toString();
			if (str) input.value += (input.value != "" ? "," : "")+str;
		});
	}

	var E = YAHOO.util.Event;
	E.addListener("orgchart_unselect_all_<?php echo $id; ?>", "click", function(e) {
		E.preventDefault(e);
		var t = TreeView_orgchart_selector_tree_<?php echo $id; ?>;
		t.unselectAll();
	});
});
</script>
<?php } //endif ?>
<?php

$_languages = array(
	'_ROOT' => Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', 'organization_chart') ),
	'_YES' => Lang::t('_CONFIRM', 'organization_chart'),
	'_NO' => Lang::t('_UNDO', 'organization_chart'),
	'_LOADING' => Lang::t('_LOADING', 'standard'),
	'_AREYOUSURE'=> Lang::t('_AREYOUSURE', 'organization_chart'),
	'_NAME' => Lang::t('_NAME', 'standard'),
	'_RADIO_NO' => Lang::t('_NO', 'standard'),
	'_RADIO_YES' => Lang::t('_YES', 'standard'),
	'_RADIO_INHERIT' => Lang::t('_INHERIT', 'standard')
);

$orgchart_rel_action = '<a class="" id="orgchart_unselect_all_'.$id.'" href="javascript:;" '
	.' title="'.Lang::t('_UNSELECT_ALL', 'organization_chart').'">'
	.'<span>'.Lang::t('_UNSELECT_ALL', 'organization_chart').'</span>'
	.'</a>';

$this->widget('tree', array(
	'id' => 'orgchart_selector_tree_'.$id,
	'ajaxUrl' => 'ajax.adm_server.php?r=widget/userselector/getorgcharttreedata',
	'treeClass' => 'SelectorTree',
	'treeFile' => Get::rel_path('base').'/widget/tree/selectortree.js',
	'languages' => $_languages,
	'rootNodeId' => isset($root_node_id) ? $root_node_id : 0,
	'initialSelectedNode' => (int)$selected_node,
	'initialSelectorData' => $initial_selection,
	'canSelectRoot' => isset($can_select_root) ? (bool)$can_select_root : true,
	'show' => 'tree',
	'dragDrop' => false,
	'rel_action' => $orgchart_rel_action
));

if ($use_form_input && $separate_input) echo '<input type="hidden" id="userselector_input_'.$id.'_orgchart" name="userselector_input['.$id.'][orgchart]" value="" />';

?>