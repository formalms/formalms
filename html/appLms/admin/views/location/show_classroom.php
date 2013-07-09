<?php

$_back_url = 'index.php?r=alms/location/show';
$_add_url = 'index.php?r=alms/location/addclassroom&amp;id_location='.(int)$id_location;

Get::title(array(
	$_back_url => Lang::t('_LOCATION', 'classroom'),
	Lang::t('_CLASSROOM', 'classroom')
));

?>
<div class="std_block">
<?php

echo getBackUi('index.php?r=alms/location/show', Lang::t('_BACK'));

$this->widget('table', array(
	'id'			=> 'classroomlist',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/location/getclassroom&location_id='.(int)$id_location,
	'sort'			=> 'classroom',
	'columns'		=> array(
		array('key' => 'classroom',
			'label' => Lang::t('_CLASSROOM', 'lms'),
			'sortable' => true ),
		array('key' => 'classroom_schedule',
			'label' => '<span class="ico-sprite subs_wait"><span>'.Lang::t('_SCHEDULE', 'standard').'</span></span>',
			'formatter' => 'Schedule.SchedFormatter',
			'className' => 'img-cell' ),
		array('key' => 'classroom_mod',
			'label' => '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'standard').'</span></span>',
			'formatter' => 'Classroom.modFormatter',
			'className' => 'img-cell' ),
		array('key' => 'classroom_del',
			'label' => '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>',
			'formatter' => 'stdDelete',
			'className' => 'img-cell' ),
	),
	'fields'		=> array('id_classroom','classroom', 'classroom_mod', 'classroom_del'),
	'delDisplayField' => 'classroom',
	'rel_actions'	=> array(
		'<a id="addclassroom_top" href="'.$_add_url.'" class="ico-wt-sprite subs_add" title="'.Lang::t('_ADD', 'standard').'"><span>'.Lang::t('_ADD', 'standard').'</span></a>',
		'<a id="addclassroom_bottom" href="'.$_add_url.'" class="ico-wt-sprite subs_add" title="'.Lang::t('_ADD', 'standard').'"><span>'.Lang::t('_ADD', 'standard').'</span></a>',
	)
));

echo getBackUi('index.php?r=alms/location/show', Lang::t('_BACK'));

?>
<script type="text/javascript">

var Classroom = {
	modFormatter: function(elLiner, oRecord, oColumn, oData) {
		var id = oRecord.getData("id_classroom");
		elLiner.innerHTML = '<a href="index.php?r=alms/location/modclassroom&amp;id_classroom='+id+'" '
			+'class="ico-sprite subs_mod" title="<?php echo Lang::t('_MOD', 'standard'); ?>">'
			+'<span><?php echo Lang::t('_MOD', 'standard'); ?></span></a>';}
};	

var Schedule = {
	SchedFormatter: function(elLiner, oRecord, oColumn, oData) {
		var id = oRecord.getData("id_classroom");
		elLiner.innerHTML = '<a href="index.php?r=alms/location/show_calendar&amp;id_classroom='+id+'" '
			+'class="ico-sprite subs_wait" title="<?php echo Lang::t('_SCHEDULE', 'standard'); ?>">'
			+'<span><?php echo Lang::t('_SCHEDULE', 'standard'); ?></span></a>';}

};


</script>
</div>
