<?php echo getTitleArea($title_arr); ?>
<div class="std_block">
<?php if (isset($result_message)) echo $result_message; ?>
<?php

echo getBackUi('index.php?r=adm/functionalroles/show', Lang::t('_BACK', 'standard'));

//--- SEARCH FILTER -------

$this->widget('tablefilter', array(
	'id' => 'fncrole_users_filter',
	'filter_text' => isset($filter_text) ? $filter_text : "",
	'js_callback_set' => 'Users.setFilter',
	'js_callback_reset' => 'Users.resetFilter'
));


//--- TABLE -------

$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>';
$icon_chart = '<span class="ico-sprite subs_chart"><span>'.Lang::t('_GAP_ANALYSIS', 'fncroles').'</span></span>';

$columns = array(
	array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'sortable' => true, 'formatter' => 'Users.labelFormatter'),
	array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'sortable' => true, 'formatter' => 'Users.labelFormatter'),
	array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'sortable' => true, 'formatter' => 'Users.labelFormatter'),
	array('key' => 'gap', 'label' => $icon_chart, 'formatter' => 'Users.gapAnalisysFormatter', 'className' => 'img-cell')
);
if ($permissions['mod']) $columns[] = array('key' => 'del', 'label' => $icon_del, 'formatter' => 'Users.deleteFormatter', 'className' => 'img-cell');



$rel_action_over = "";
$rel_action_bottom = "";

if ($permissions['mod']) {
	$rel_action_over = '<a id="sel_users_over" class="ico-wt-sprite subs_add" title="'.Lang::t('_ASSIGN_USERS', 'standard').'" '
		.'href="index.php?r=adm/functionalroles/sel_users&id_fncrole='.(int)$id_fncrole.'">'
		.'<span>'.Lang::t('_ASSIGN_USERS', 'standard').'</span></a>'
		.'<a id="del_users_over" class="ico-wt-sprite subs_del" '
		.'href="ajax.adm_server.php?r=adm/functionalroles/del_users&id_fncrole='.(int)$id_fncrole.'">'
		.'<span>'.Lang::t('_DEL_SELECTED', 'admin_directory').'</span></a>';

	$rel_action_bottom = '<a id="sel_users_bottom" class="ico-wt-sprite subs_add" title="'.Lang::t('_ASSIGN_USERS', 'standard').'" '
		.'href="index.php?r=adm/functionalroles/sel_users&id_fncrole='.(int)$id_fncrole.'">'
		.'<span>'.Lang::t('_ASSIGN_USERS', 'standard').'</span></a>'
		.'<a id="del_users_bottom" class="ico-wt-sprite subs_del" '
		.'href="ajax.adm_server.php?r=adm/functionalroles/del_users&id_fncrole='.(int)$id_fncrole.'">'
		.'<span>'.Lang::t('_DEL_SELECTED', 'admin_directory').'</span></a>';

	$rel_action_over .= '<span class="ma_selected_users">'
		.'<b id="num_users_selected_top">'.(int)(isset($num_users_selected) ? $num_users_selected : '0').'</b> '.Lang::t('_SELECTED', 'admin_directory')
		.'</span>';
	$rel_action_bottom .= '<span class="ma_selected_users">'
		.'<b id="num_users_selected_bottom">'.(int)(isset($num_users_selected) ? $num_users_selected : '0').'</b> '.Lang::t('_SELECTED', 'admin_directory')
		.'</span>';
}


$params = array(
	'id'			=> 'users_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/functionalroles/getusertabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'userid',
	'dir'			=> 'asc',
	'generateRequest' => 'Users.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('id', 'userid', 'firstname', 'lastname', 'del', 'is_group'),
	'delDisplayField' => 'userid',
	'stdSelection' => $permissions['mod'] ? true : false,
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'events' => array(
		'initEvent' => 'Users.initEvent',
		'beforeRenderEvent' => 'Users.beforeRenderEvent',
		'postRenderEvent' => 'Users.postRenderEvent'
	)
);



$this->widget('table', $params);

echo getBackUi('index.php?r=adm/functionalroles/show', Lang::t('_BACK', 'standard'));

?>
</div>
<script type="text/javascript">
var Users = {
	idFncrole: 0,
	oLangs: new LanguageManager(),
	filterText: "",
	imagesPath: "",

	init: function(oConfig) {
		if (oConfig.idFncrole) this.idFncrole = oConfig.idFncrole;
		if (oConfig.filterText) this.filterText = oConfig.filterText;
		if (oConfig.langs) this.oLangs.set(oConfig.langs);

		var D = YAHOO.util.Dom, E = YAHOO.util.Event, U = Users;
		var L = U.oLangs;

		//multi delete confirm dialog
		E.onDOMReady(function() {
			E.addListener(["del_users_over", "del_users_bottom"], "click", function(e) {
				var body, count_sel = DataTableSelector_users_table.num_selected;
				if (count_sel > 0) {
					body = '<form method="POST" id="usertable_multidel_dialog_form" action="'+this.href+'">'
						+'<p>'+L.get('_DEL')+': '+count_sel+' '+L.get('_USERS')+'</p>'
						+'<input type="hidden" name="users" value="'+DataTableSelector_users_table.toString()+'" />'
						+'</form>';
				} else {
					body = '<p>'+L.get('_EMPTY_SELECTION')+'</p>';
				}
				var oDialog = CreateDialog("usertable_multiDeleteDialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					confirmOnly: (count_sel > 0 ? false : true),
					header: L.get('_AREYOUSURE'),
					body: body,
					callback: function(o) {
						if (o.list) {
							var i;
							for (i=0; i<o.list.length; i++)
								DataTableSelector_users_table.remsel(o.list[i]);
						}
						this.destroy();
						DataTable_users_table.refresh();
					}
				});
				oDialog.call(this, e);
			});
		});
	},

	setFilter: function() {
		Users.filterText = this.value;
		DataTable_users_table.refresh();
	},

	resetFilter: function() {
		this.value = "";
		Users.filterText = "";
		DataTable_users_table.refresh();
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
				"&dir=" + dir +
				"&id_fncrole=" + Users.idFncrole +
				"&filter_text=" + Users.filterText;
	},

	gapAnalisysFormatter: function(elLiner, oRecord, oColumn, oData) {
		var url = 'index.php?r=adm/functionalroles/user_gapanalisys'
			+'&id_fncrole='+Users.idFncrole+'&id_user='+oRecord.getData("id")+'&from_gap=1';
		elLiner.innerHTML = '<a class="ico-sprite subs_chart" href="'+url+'" '
			+'title="'+Users.oLangs.get('_GAP_ANALYSIS')+'">'
			+'<span>'+Users.oLangs.get('_GAP_ANALYSIS')+'</span></a>';
	},

	deleteFormatter: function(elLiner, oRecord, oColumn, oData) {
		if (oRecord.getData("is_group")) {
			elLiner.innerHTML = '';
		} else {
			YAHOO.widget.DataTable.Formatter.stdDelete.call(this, elLiner, oRecord, oColumn, oData);
		}
	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="users_table_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	},

	deleteUsersCallback: function(o) {
		this.destroy();
	},

	setNumUserSelected: function(num) {
		var prefix = "num_users_selected_", D = YAHOO.util.Dom;
		D.get(prefix+"top").innerHTML = num;
		D.get(prefix+"bottom").innerHTML = num;
	},

	initEvent: function() {
		var updateSelected = function() {
			Users.setNumUserSelected(this.num_selected);
		};
		var ds = DataTableSelector_users_table;
		ds.subscribe("add", updateSelected);
		ds.subscribe("remove", updateSelected);
		ds.subscribe("reset", updateSelected);
	},

	beforeRenderEvent: function() {
		var elList = YAHOO.util.Selector.query('a[id^=users_table_del_]');
		YAHOO.util.Event.purgeElement(elList);
	},

	postRenderEvent: function() {
		var elList = YAHOO.util.Selector.query('a[id^=users_table_del_]');
		YAHOO.util.Event.addListener(elList, "click", function(e) {
			var oDt = DataTable_users_table;
			var oRecord = oDt.getRecord(this);
			CreateDialog("users_table_del_dialog", {
				width: "500px",
				modal: true,
				close: true,
				visible: false,
				fixedcenter: true,
				constraintoviewport: true,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: false,
				header: Users.oLangs.get('_AREYOUSURE'),
				body: '<div id="users_table_del_dialog_message"></div>'
					+'<form method="POST" id="users_table_del_dialog_form" action="'+this.href+'">'
					+'<p>'+Users.oLangs.get('_DEL')+':&nbsp;<b>'+oRecord.getData("userid")+'</b></p>'
					+'</form>',
				callback: function() {
					this.destroy();
					oDt.refresh();
				}
			}).call(this, e);
		});
	}
}



Users.init({
	idFncrole: <?php echo (int)$id_fncrole; ?>,
	filterText: "<?php echo isset($filter_text) ? $filter_text : ""; ?>",
	imagesPath: "<?php echo Get::tmpl_path('base'); ?>",
	langs: {
		_AREYOUSURE: "<?php echo Lang::t('_AREYOUSURE', 'standard'); ?>",
		_DEL: "<?php echo Lang::t('_DEL', 'standard'); ?>",
		_GAP_ANALYSIS: "<?php echo Lang::t('_GAP_ANALYSIS', 'fncroles'); ?>",
		_EMPTY_SELECTION: "<?php echo Lang::t('_EMPTY_SELECTION', 'admin_directory'); ?>",
		_USERS: "<?php echo Lang::t('_USERS', 'standard'); ?>"
	}
});

</script>