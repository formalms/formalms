<?php Get::title(Lang::t('_LANGUAGE', 'admin_lang')); ?>

<table class="table table-bordered display" style="width:100%" id="langlist"></table>
<br>
<a id="addlang_bottom" href="#" class="ico-wt-sprite subs_add" title="<?php echo Lang::t('_ADD', 'standard'); ?>"><span><?php echo Lang::t('_ADD', 'standard'); ?></span></a>
<a href="index.php?r=adm/lang/import" class="ico-wt-sprite subs_import" title="<?php echo Lang::t('_IMPORT', 'standard'); ?>"><span><?php echo Lang::t('_IMPORT', 'standard'); ?></span></a>

<?php

$this->widget('dialog', array(
	'id' => 'lang_add',
	'dynamicContent' => true,
	'ajaxUrl' => 'ajax.adm_server.php?r=adm/lang/addmask',
	'callback' => 'function() { this.destroy(); location.reload(); }',
	'callEvents' => array(
		array('caller' => 'addlang_bottom', 'event' => 'click')
	)
));
?>
<script type="text/javascript">
	$(function() {
		var body = <?php echo json_encode($langList); ?>;

		var columns = [{
				data: 'lang_code',
				title: '<?php echo Lang::t('_LANGUAGE ', 'admin_lang '); ?>',
				sortable: true
			},
			{
				data: 'lang_description',
				title: '<?php echo Lang::t('_DESCRIPTION ', 'admin_lang '); ?>',
				sortable: true
			},
			{
				data: 'lang_direction',
				title: '<?php echo Lang::t('_ORIENTATION ', 'admin_lang '); ?>',
				sortable: true
			},
			{
				data: 'lang_stats',
				title: '<?php echo Lang::t('_STATISTICS ', 'admin_lang '); ?>',
				sortable: true
			},
			{
				data: 'lang_translate',
				title: '<span class="ico-sprite subs_elem"><span><?php echo Lang::t("_TRANSLATELANG", "admin_lang"); ?></span></span>',
				sortable: true
			},
			{
				data: 'lang_diff',
				title: '<span class="ico-sprite subs_diff"><span><?php echo Lang::t("_DIFF_LANG", "admin_lang"); ?></span></span>',
				sortable: true
			},
			{
				data: 'lang_export',
				title: '<span class="ico-sprite subs_download"><span><?php echo Lang::t("_EXPORT_XML", "admin_lang"); ?></span></span>',
				sortable: true
			},
			{
				data: 'lang_mod',
				title: '<span class="ico-sprite subs_mod"><span><?php echo Lang::t("_MOD", "admin_lang"); ?></span></span>',
				sortable: true
			},
			{
				data: 'lang_del',
				title: '<span class="ico-sprite subs_del"><span><?php echo Lang::t("_DEL", "admin_lang"); ?></span></span>',
				sortable: true
			}

		];
		var rows = [];

		body.forEach(function(item, k) {
			link = '<a id="' + item.id + '" href="' + item.lang_translate + '" class="ico-sprite subs_elem" title="<?php echo Lang::t("_TRANSLATELANG", "admin_lang"); ?>"><span></span></a>'
			item.lang_translate = link;
			link = '<a id="' + item.id + '" href="' + item.lang_diff + '" class="ico-sprite subs_diff" title="<?php echo Lang::t("_DIFF_LANG", "admin_lang"); ?>"><span></span></a>'
			item.lang_diff = link;
			link = '<a id="' + item.id + '" href="' + item.lang_export + '" class="ico-sprite subs_download" title="<?php echo Lang::t("_EXPORT_XML", "admin_lang"); ?>"><span></span></a>'
			item.lang_export = link;
			link = '<a id="' + item.id + '" href="' + item.lang_mod + '" class="ico-sprite subs_mod" title="<?php echo Lang::t("_MOD", "admin_lang"); ?>"><span></span></a>'
			item.lang_mod = link;
			link = '<a id="' + item.id + '" href="' + item.lang_del + '" class="ico-sprite subs_del" title="<?php echo Lang::t("_DEL", "admin_lang"); ?>"><span></span></a>'
			item.lang_del = link;

			rows.push(Object.assign({}, item));
		});

		t = $('#langlist').FormaTable({
			rowId: function(row) {
				return row[0];
			}, // cambia
			scrollX: true,
			processing: true,
			serverSide: false,
			paging: true,
			searching: true,
			columns,
			data: rows,
			dom: 'Bfrtip',
			stateSave: true,
			deferRender: true,
		});
	});
</script>
</div>