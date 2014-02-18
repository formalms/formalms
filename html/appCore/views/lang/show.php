<?php Get::title(Lang::t('_LANGUAGE', 'admin_lang')); ?>
<div class="std_block">
<?php
$this->widget('table', array(
	'id'			=> 'langlist',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/lang/getlang',
	'sort'			=> 'lang_code',
	'columns'		=> array(
		array('key' => 'lang_code', 
			'label' => Lang::t('_LANGUAGE', 'admin_lang'),
			'sortable' => true ),
		array('key' => 'lang_description', 
			'label' => Lang::t('_DESCRIPTION', 'admin_lang'),
			'sortable' => true ),
		array('key' => 'lang_direction',
			'label' => Lang::t('_ORIENTATION', 'admin_lang'),
			'className' => 'min-cell',
			'sortable' => true ),
		array('key' => 'lang_stats',
			'label' => Lang::t('_STATISTICS', 'admin_lang'),
			'className' => 'img-cell',
			'sortable' => true ),
		array('key' => 'lang_translate', 
			'label' => '<span class="ico-sprite subs_elem"><span>'.Lang::t('_TRANSLATELANG', 'admin_lang').'</span></span>',
			'formatter' => 'TranslateFormatter',
			'className' => 'img-cell' ),
		array('key' => 'lang_export',
			'label' => '<span class="ico-sprite subs_download"><span>'.Lang::t('_EXPORT_XML', 'admin_lang').'</span></span>',
			'formatter' => 'ExportFormatter',
			'className' => 'img-cell' ),
		array('key' => 'lang_mod', 
			'label' => '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'admin_lang').'</span></span>',
			'formatter' => 'stdModify',
			'className' => 'img-cell' ),
		array('key' => 'lang_del',
			'label' => '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'admin_lang').'</span></span>',
			'formatter' => 'stdDelete',
			'className' => 'img-cell' ),
	),
	'fields'		=> array('lang_code', 'lang_description','lang_direction', 'lang_stats', 'lang_translate', 'lang_export', 'lang_mod', 'lang_del'),
	'delDisplayField' => 'lang_code',
	'rel_actions'	=> array(
		'<a id="addlang_top" href="ajax.adm_server.php?r=adm/lang/addmask" class="ico-wt-sprite subs_add" title="'.Lang::t('_ADD', 'standard').'"><span>'.Lang::t('_ADD', 'standard').'</span></a>'
		.'<a href="index.php?r=adm/lang/import" class="ico-wt-sprite subs_import" title="'.Lang::t('_IMPORT', 'standard').'"><span>'.Lang::t('_IMPORT', 'standard').'</span></a>',
		'<a id="addlang_bottom" href="ajax.adm_server.php?r=adm/lang/addmask" class="ico-wt-sprite subs_add" title="'.Lang::t('_ADD', 'standard').'"><span>'.Lang::t('_ADD', 'standard').'</span></a>'
		.'<a href="index.php?r=adm/lang/import" class="ico-wt-sprite subs_import" title="'.Lang::t('_IMPORT', 'standard').'"><span>'.Lang::t('_IMPORT', 'standard').'</span></a>',
	)
));

$this->widget('dialog', array(
	'id' => 'lang_add',
	'dynamicContent' => true,
	'ajaxUrl' => 'ajax.adm_server.php?r=adm/lang/addmask',
	'callback' => 'function() { this.destroy(); DataTable_langlist.refresh(); }',
	'callEvents' => array(
		array('caller' => 'addlang_top', 'event' => 'click'),
		array('caller' => 'addlang_bottom', 'event' => 'click')
	)
));
?>
<script type="text/javascript">

function TranslateFormatter(elLiner, oRecord, oColumn, oData) {
		var id = this.getTableEl().parentNode.id+'_translate_'+oRecord.getData("id");
		if(oData) elLiner.innerHTML = '<a id="'+id+'" href="'+oData+'" class="ico-sprite subs_elem" title="<?php echo Lang::t('_TRANSLATELANG', 'admin_lang'); ?>"><span></span></a>';
		else elLiner.innerHTML = '';
}
function ExportFormatter(elLiner, oRecord, oColumn, oData) {
		var id = this.getTableEl().parentNode.id+'_translate_'+oRecord.getData("id");
		if(oData) elLiner.innerHTML = '<a id="'+id+'" href="'+oData+'" class="ico-sprite subs_download" title="<?php echo Lang::t('_EXPORT', 'admin_lang'); ?>"><span></span></a>';
		else elLiner.innerHTML = '';
}

</script>
</div>