<?php
echo getTitleArea(array(
	'index.php?r=alms/enrollrules/show' => Lang::t('_ENROLLRULES', 'enrollrules'),
	Lang::t('_MANAGE', 'enrollrules').': '.$rule->title
));
?>
<div class="std_block">
<script type="text/javascript">
var coursecheckbox = function(elLiner, oRecord, oColumn, oData) {
	var id_entity = oRecord.getData("id_entity");
	try{
		var txt = '<input type="checkbox" id="entity_course'+id_entity+'_'+oColumn.key+'" name="entity_course['+id_entity+']['+oColumn.key+']" value="1"';
		if(oData == 1) txt = txt+' checked="checked"';
		txt = txt+' />';
		elLiner.innerHTML = txt;
	}catch(e) {}
}
</script>
<?php
echo Form::openForm('entity_course_rule', 'index.php?r=alms/enrollrules/savebaserule&amp;id_rule='.$id_rule);
$this->widget('table', array(
	'id'			=> 'showrule',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/enrollrules/getbaserule&id_rule='.$id_rule,
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> false,
	'dir'			=> 'asc',
	'columns'		=> $columns,
	'fields'		=> $keys,
	'show'			=> 'table',
	'rel_actions'	=> array(
		'<a id="rule_course_over" class="ico-wt-sprite subs_add" href="index.php?r=alms/enrollrules/addcourses&amp;id_rule='.$id_rule.'&amp;load=1"><span>'.Lang::t('_ADD_COURSES', 'enrollrules').'</span></a>',

		'<a id="rule_course_below" class="ico-wt-sprite subs_add" href="index.php?r=alms/enrollrules/addcourses&amp;id_rule='.$id_rule.'&amp;load=1"><span>'.Lang::t('_ADD_COURSES', 'enrollrules').'</span></a>'
	),
));
echo Form::openButtonSpace()
	.Form::getButton('save', 'save', Lang::t('_SAVE', 'enrollrule'))
	.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'enrollrule'))
	.Form::closeButtonSpace()
	.Form::closeForm();
?>
</div>