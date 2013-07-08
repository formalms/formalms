<?php Get::title(Lang::t('_LOCATION', 'classroom')); ?>
<div class="std_block">

	<?php
$rel_action = array();
if($this->perm['mod'])
	$rel_action = array('<a id="addlocation_top" href="ajax.adm_server.php?r=alms/location/addmask" class="ico-wt-sprite subs_add" title="'.Lang::t('_ADD', 'standard').'"><span>'.Lang::t('_ADD', 'standard').'</span></a>',
						'<a id="addlocation_bottom" href="ajax.adm_server.php?r=alms/location/addmask" class="ico-wt-sprite subs_add" title="'.Lang::t('_ADD', 'standard').'"><span>'.Lang::t('_ADD', 'standard').'</span></a>',
						);

$this->widget('table', array(
	'id'			=> 'locationlist',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/location/getlocation',
	'sort'			=> 'location',
	'columns'		=> array(
		array('key' => 'location',
			'label' => Lang::t('_LOCATION', 'lms'),
			'sortable' => true ),
		array('key' => 'location_classroom',
			'label' => '<span class="ico-sprite subs_elem"><span>'.Lang::t('_CLASSROOM', 'classroom').'</span></span>',
			'formatter' => 'locationclassroom',
			'className' => 'img-cell' ),
		array('key' => 'location_mod',
			'label' => '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'standard').'</span></span>',
			'formatter' => 'stdModify',
			'className' => 'img-cell' ),
		array('key' => 'location_del',
			'label' => '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>',
			'formatter' => 'stdDelete',
			'className' => 'img-cell' ),
	),
	'fields' => array('id_location', 'location', 'can_manage_classrooms', 'location_mod', 'location_del'),
	'delDisplayField' => 'location',
	'rel_actions'	=> $rel_action
));

	$this->widget('dialog', array(
		'id' => 'location_add',
		'dynamicContent' => true,
		'ajaxUrl' => 'ajax.adm_server.php?r=alms/location/addmask',
		'callback' => 'function() { this.destroy(); DataTable_locationlist.refresh(); }',
		'callEvents' => array(
			array('caller' => 'addlocation_top', 'event' => 'click'),
			array('caller' => 'addlocation_bottom', 'event' => 'click')
		)
	));


?>
<script type="text/javascript">

function locationclassroom(elLiner, oRecord, oColumn, oData) {
	if (oRecord.getData("can_manage_classrooms") <= 0) {
		elLiner.innerHTML = '';
	} else {
		var id_location = oRecord.getData("id_location");
		elLiner.innerHTML = '<a href="index.php?r=alms/location/show_classroom&amp;id_location='+id_location+'" '
			+'class="ico-sprite subs_elem" title="<?php echo Lang::t('_CLASSROOM', 'classroom'); ?>">'
			+'<span><?php echo Lang::t('_CLASSROOM', 'classroom'); ?></span></a>';
	}
}

</script>
</div>