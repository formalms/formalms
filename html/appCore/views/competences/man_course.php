<?php echo getTitleArea($title_arr); ?>
<script type="text/javascript">
var CourseCompetences = {
	filterText: "",

	scoreFormatter: function(elLiner, oRecord, oColumn, oData) {
		if (oData > 0)
			elLiner.innerHTML = oData;
		else
			elLiner.innerHTML = '<span class="red">0</span>&nbsp;<span class="ico-sprite fd_notice">' +
				'<span><?php echo Lang::t('_NOT_ASSIGNED', 'competences'); ?></span></span>';
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
				"&id_course=" + <?php echo (int)$id_course; ?> +
				"&filter_text=" + CourseCompetences.filterText;
	}
}

YAHOO.util.Event.onDOMReady(function() {
	YAHOO.util.Event.addListener("filter_set", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		CourseCompetences.filterText = YAHOO.util.Dom.get("filter_text").value;
		DataTable_course_competences_table.refresh();
	});

	YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Dom.get("filter_text").value = "";
		CourseCompetences.filterText = "";
		DataTable_course_competences_table.refresh();
	});

	YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
		switch (YAHOO.util.Event.getCharCode(e)) {
			case 13: {
				YAHOO.util.Event.preventDefault(e);
				CourseCompetences.filterText = YAHOO.util.Dom.get("filter_text").value;
				DataTable_course_competences_table.refresh();
			} break;
		}
	});
});
</script>
<div class="std_block">
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
	array('key' => 'name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard'), 'sortable' => true),
	array('key' => 'typology', 'label' => Lang::t('_TYPOLOGY', 'standard'), 'sortable' => true),
	array('key' => 'type', 'label' => Lang::t('_TYPE', 'standard'), 'sortable' => true),
	array('key' => 'score', 'label' => Lang::t('_SCORE', 'competences'), 'sortable' => true, 'className' => 'img-cell', 'formatter' => 'CourseCompetences.scoreFormatter'),
	array('key' => 'del', 'label' => $icon_del, 'formatter'=>'stdDelete', 'className' => 'img-cell')
);

$rel_action_over = '<a id="sel_course_competences_over" class="ico-wt-sprite subs_add" '
	.'href="index.php?r='.$this->base_link_competence.'/assign_to_course&id_course='.(int)$id_course.'">'
	.'<span>'.Lang::t('_ADD', 'competences').'</span></a>'
	.($has_scores ? '<a id="mod_course_competences_over" class="ico-wt-sprite subs_mod" '
	.'href="index.php?r='.$this->base_link_competence.'/mod_course_competences&id_course='.(int)$id_course.'">'
	.'<span>'.Lang::t('_MOD', 'competences').'</span></a>' : '');

$rel_action_bottom = '<a id="sel_course_competences_bottom" class="ico-wt-sprite subs_add" '
	.'href="index.php?r='.$this->base_link_competence.'/assign_to_course&id_course='.(int)$id_course.'">'
	.'<span>'.Lang::t('_ADD', 'competences').'</span></a>'
	.($has_scores ? '<a id="mod_course_competences_over" class="ico-wt-sprite subs_mod" '
	.'href="index.php?r='.$this->base_link_competence.'/mod_course_competences&id_course='.(int)$id_course.'">'
	.'<span>'.Lang::t('_MOD', 'competences').'</span></a>' : '');

$this->widget('table', array(
	'id'			=> 'course_competences_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r='.$this->base_link_competence.'/getcoursetabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'name',
	'dir'			=> 'asc',
	'generateRequest' => 'CourseCompetences.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('id', 'name', 'description', 'typology', 'type', 'score', 'del'),
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'delDisplayField' => 'name'
));

?>
</div>