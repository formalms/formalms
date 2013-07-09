<?php
echo getTitleArea(array(
	'index.php?r=adm/competences/show' => Lang::t('_COMPETENCES', 'competences'),
	Lang::t('_USERS', 'competences')
));
?>
<script type="text/javascript">
var CompetenceUsers = {

	showRequired: 0,
	filterText: "",

	requiredFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = oData > 0 ? '<span class="ico-sprite subs_actv">' +
			'<span><?php echo Lang::t('_MANDATORY', 'competences'); ?></span></span>' : '';
	},

	historyFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<a href="index.php?r=adm/competences/user_history' + '&id_user=' + oRecord.getData("idst") +
			'&id_competence=<?php echo (int)$competence_info->id_competence; ?>" class="ico-sprite subs_elem" ' +
			'title="'+<?php echo Lang::t('_HISTORY', 'competences'); ?>+'"><span></span></a>';
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
				"&dir=" + dir+
				"&id_competence=" + <?php echo (int)$competence_info->id_competence; ?> +
				"&required=" + CompetenceUsers.showRequired +
				"&filter_text=" + CompetenceUsers.filterText;
	},

	editorSaveEvent: function(oArgs) {
		var oEditor = oArgs.editor;
		var new_score = oArgs.newData;
		var old_score = oArgs.oldData;
		var id_user = oEditor.getRecord().getData("idst");
		var callback = {
			success: function(o) {
				var res = YAHOO.lang.JSON.parse(o.responseText);
				if (res.success) {
					
				}
			},
			failure: function() {}
		};

		var url = "ajax.adm_server.php?r=adm/competences/change_user_score";
		var post = "id_competence=<?php echo (int)$competence_info->id_competence; ?>"
					+"&id_user=" + id_user
					+"&new_score=" + new_score
					+"&old_score=" + old_score;

		YAHOO.util.Connect.asyncRequest("POST", url, callback, post);
	}
};

YAHOO.util.Event.onDOMReady(function() {
	YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
		switch (YAHOO.util.Event.getCharCode(e)) {
			case 13: {
				YAHOO.util.Event.preventDefault(e);
				CompetenceUsers.filterText = this.value;
				DataTable_competence_users_table.refresh();
			} break;
		}
	});

	YAHOO.util.Event.addListener("filter_set", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		CompetenceUsers.filterText = YAHOO.util.Dom.get("filter_text").value;
		DataTable_competence_users_table.refresh();
	});

	YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Dom.get("filter_text").value = "";
		CompetenceUsers.filterText = "";
		DataTable_competence_users_table.refresh();
	});
});
</script>
<div class="std_block">
<?php echo $result_message; ?>

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

$icon_history = '<span class="ico-sprite subs_elem"><span>'.Lang::t('_HISTORY', 'standard').'</span></span>';
$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_UNASSIGN', 'competences').'</span></span>';

$columns = array();
$columns[] = array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'sortable' => true);
$columns[] = array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'sortable' => true);
$columns[] = array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'sortable' => true);
if ($competence_info->type == 'score') $columns[] = array('key' => 'score', 'label' => Lang::t('_SCORE', 'standard'), 'sortable' => true, 'className' => 'img-cell',
				'editor' => 'new YAHOO.widget.TextboxCellEditor({ validator: YAHOO.widget.DataTable.validateNumber })');
$columns[] = array('key' => 'last_assign_date', 'label' => Lang::t('_DATE_LAST_COMPLETE', 'subscribe'), 'sortable' => true, 'className' => 'img-cell');
//$columns[] = array('key' => 'date_expire', 'label' => Lang::t('_EXPIRATION_DATE', 'competences'), 'sortable' => true);
//$columns[] = array('key' => 'is_required', 'label' => Lang::t('_IS_REQUIRED', 'competences'), 'formatter'=>'CompetenceUsers.requiredFormatter', 'className' => 'img-cell');
//$columns[] = array('key' => 'history', 'label' => $icon_history, 'formatter'=>'CompetenceUsers.historyFormatter', 'className' => 'img-cell');
$columns[] = array('key' => 'unassign', 'label' => $icon_del, 'formatter'=>'stdDelete', 'className' => 'img-cell');

$rel_actions = '<a class="ico-wt-sprite subs_add" title="'.Lang::t('_ASSIGN_USERS', 'standard').'" '
	.'href="index.php?r=adm/competences/assign_users&id_competence='.(int)$competence_info->id_competence.'">'
	.'<span>'.Lang::t('_ASSIGN_USERS', 'standard').'</span></a>'
	.($competence_info->type == 'score' && $count_users > 0
		? '<a class="ico-wt-sprite subs_mod" title="'.Lang::t('_EDIT_SCORE', 'competences').'" '
			.'href="index.php?r=adm/competences/mod_users&id_competence='.(int)$competence_info->id_competence.'">'
			.'<span>'.Lang::t('_EDIT_SCORE', 'competences').'</span></a>'
		: '');

$this->widget('table', array(
	'id'			=> 'competence_users_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/competences/getuserstabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'userid',
	'dir'			=> 'asc',
	'generateRequest' => 'CompetenceUsers.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('idst', 'userid', 'firstname', 'lastname', 'last_assign_date', 'date_expire', 'is_required', 'score', 'unassign'),
	'delDisplayField' => 'userid',
	'rel_actions' => $rel_actions,
	'editorSaveEvent' => 'CompetenceUsers.editorSaveEvent'
));

?>
</div>