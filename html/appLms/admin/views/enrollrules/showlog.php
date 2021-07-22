<?php Get::title(array(
	'index.php?r=alms/enrollrules/show' => Lang::t('_ENROLLRULES', 'enrollrules'),
	Lang::t('_SHOW_LOGS', 'enrollrules')
)); ?>
<div class="std_block">
	<script type="text/javascript">
	var EnrollLog = {
		details: function(elLiner, oRecord, oColumn, oData) {
			if(oData) elLiner.innerHTML = '<a href="'+oData+'"><span><?php echo Lang::t('_DETAILS', 'enrollrules'); ?></span></a>';
			else elLiner.innerHTML = '';
		},
		rollback: function(elLiner, oRecord, oColumn, oData) {
			var id = 'er_rollbak_'+oRecord.getData("id");
			if(oData) elLiner.innerHTML = '<a id="'+id+'" href="'+oData+'"><span><?php echo Lang::t('_ROLLBACK', 'enrollrules'); ?></span></a>';
			else elLiner.innerHTML = '';
		},
		tablebefore: function() {
			var elList = YAHOO.util.Selector.query('a[id^=er_rollbak_]');
			YAHOO.util.Event.purgeElement(elList);
		},
		tableafter: function() {
			var elList = YAHOO.util.Selector.query('a[id^=er_rollbak_]');
			YAHOO.util.Event.addListener(elList, "click", function(e) {
				var oRecord = DataTable_enrolllog.getRecord(this);
				CreateDialog("er_rollback_dialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					header: "<?php echo Lang::t('_AREYOUSURE'); ?>",
					body: '<div id="er_rollback_message"></div>'
						+'<form method="post" id="er_rollback_form" action="'+this.href+'">'
						+'<b><?php echo Lang::t('_ROLLBACK', 'enrollrules'); ?>: <b>'+oRecord.getData('log_time')+'</b>'
						+'</form>',
					callback: function(o) {
						this.destroy();
						DataTable_enrolllog.refresh();
					}
				}).call(this, e);
			});
		}
	}
	</script>
<?php
$this->widget('table', array(
	'id'			=> 'enrolllog',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/enrollrules/getlog',
	'sort'			=> 'log_time',
	'dir'			=> 'desc',
	'columns'		=> array(
		//array('key' => 'id_log', 'label' => Lang::t('_ID', 'enrollrules'), 'className' => 'min-cell'),
		array('key' => 'log_time', 'label' => Lang::t('_DATE', 'enrollrules'), 'className' => 'min-cell'),
		array('key' => 'log_action', 'label' => Lang::t('_TYPE', 'enrollrules')),
		array('key' => 'log_detail', 'label' => Lang::t('_DETAILS', 'enrollrules'), 'className' => 'min-cell', 'formatter' => 'EnrollLog.details'),
		array('key' => 'rollback', 'label' => ''.Lang::t('_ROLLBACK', 'enrollrules').'', 'className' => 'img-cell', 'formatter' => 'EnrollLog.rollback'),
	),
	'fields' => array('id_log', 'log_action', 'log_time', 'log_detail', 'rollback'),
	'delDisplayField' => 'log_action',
	'events' => array(
		'beforeRenderEvent' => 'EnrollLog.tablebefore',
		'postRenderEvent' => 'EnrollLog.tableafter'
	)
));

?>
</div>