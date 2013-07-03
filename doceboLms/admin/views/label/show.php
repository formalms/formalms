<?php
echo getTitleArea(Lang::t('_LABELS', 'label'));
?>
<div class="std_block">
<?php

$_columns = array(
	array('key' => 'title', 'label' => Lang::t('_TITLE', 'label')),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'label'))
);
if ($permissions['mod']) {

	$_columns[] = array('key' => 'move_up', 'label' => '<span class="ico-sprite subs_up" title="'.Lang::t('_MOVE_UP', 'label').'"><span>'.Lang::t('_MOVE_UP', 'label').'</span></span>', 'className' => 'img-cell', 'formatter' => 'Labels.upFormatter');
	$_columns[] = array('key' => 'move_down', 'label' => '<span class="ico-sprite subs_down" title="'.Lang::t('_MOVE_DOWN', 'label').'"><span>'.Lang::t('_MOVE_DOWN', 'label').'</span></span>', 'className' => 'img-cell', 'formatter' => 'Labels.downFormatter');
	$_columns[] = array('key' => 'mod', 'label' => '<span class="ico-sprite subs_mod" title="'.Lang::t('_MOD', 'label').'"><span>'.Lang::t('_MOD', 'label').'</span></span>', 'className' => 'img-cell');
}
if ($permissions['del']) $_columns[] = array('key' => 'del', 'label' => Get::sprite('subs_del', Lang::t('_DEL', 'label')), 'formatter'=>'doceboDelete', 'className' => 'img-cell');

$_params = array(
	'id'			=> 'label_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/label/getLabels',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> '',
	'columns'		=> $_columns,
	'fields' => array('id_common_label', 'title', 'description','position', 'sequence' , 'mod', 'del'),
	'delDisplayField' => 'title',
	'events' => array(
		'beforeRenderEvent' => 'function() {
			var rlist = YAHOO.util.Selector.query("a[id^=move_]");
			for (var i=0; i<rlist.length; i++) YAHOO.util.Event.purgeElement(rlist[i]);
		}',
		'postRenderEvent' => 'function() {
			var rlist = YAHOO.util.Selector.query("a[id^=move_]");
			YAHOO.util.Event.addListener(rlist, "click", Labels.move_label);
		}'
	),
);
if ($permissions['add']) {
	$_params['rel_actions'] = '<a class="ico-wt-sprite subs_add" href="index.php?r=alms/label/add"><span>'.Lang::t('_ADD', 'standard').'</span></a>';
}

$this->widget('table', $_params);

?>
</div>
<script type="text/javascript">
var Labels = {
	move_label: function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Connect.asyncRequest("POST", this.href, {
			success:function(o) {
				DataTable_label_table.refresh();
			},
			failure:function(o) {},
			argument: [this]
		});
	},
	upFormatter: function(elLiner, oRecord, oColumn, oData) {
		var position = oRecord.getData('position');
		if (position != 'first' && position != 'firstlast')
			elLiner.innerHTML = '<a id="move_up_'+oRecord.getData('id_common_label')+'" href="ajax.adm_server.php?r=alms/label/move&dir=up&id_common_label='
				+oRecord.getData('id_common_label')
				+'" class="ico-sprite subs_up" title="<?php echo Lang::t('_MOVE_UP', 'label'); ?>"><span><?php echo Lang::t('_MOVE_UP', 'label'); ?></span></a>';
	},
	downFormatter: function(elLiner, oRecord, oColumn, oData) {
		var position = oRecord.getData('position');
		if (position != 'last' && position != 'firstlast')
			elLiner.innerHTML = '<a id="move_down_'+oRecord.getData('id_common_label')+'" href="ajax.adm_server.php?r=alms/label/move&dir=down&id_common_label='
				+oRecord.getData('id_common_label')
				+'" class="ico-sprite subs_down" title="<?php echo Lang::t('_MOVE_DOWN', 'label'); ?>"><span><?php echo Lang::t('_MOVE_DOWN', 'label'); ?></span></a>';
	}
}
</script>