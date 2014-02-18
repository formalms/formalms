<?php echo getTitleArea(Lang::t('_ADMIN_MANAGER', 'menu')); ?>
<div class="std_block">
<?php

//--- SEARCH FILTER -------

$this->widget('tablefilter', array(
	'id' => 'adminmanager',
	'filter_text' => $filter_text,
	'js_callback_set' => 'AdminManagement.setFilter',
	'js_callback_reset' => 'AdminManagement.resetFilter'
));


//--- TABLE -------

$_columns = array(
	array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'adminmanager'), 'sortable' => true),
	array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'adminmanager'), 'sortable' => true),
	array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'adminmanager'), 'sortable' => true)
);

$_profile_column = array(
	'key' => 'user_profile',
	'label' => Lang::t('_ADMIN_RULES', 'adminrules'),
	'sortable' => true,
	'formatter' => 'AdminManagement.formatUserProfile'
);
if ($permissions['assign_profile']) {
	$_profile_column['editor'] = 'new YAHOO.widget.DropdownCellEditor({'
		.'asyncSubmitter: AdminManagement.asyncSubmitter, '
		.'dropdownOptions:'.$rules_list_js.'})';
}
$_columns[] = $_profile_column;

$_img_users = Get::sprite('subs_users', Lang::t('_ASSIGN_USERS', 'adminmanager'));
$_img_courses = Get::sprite('subs_elem', Lang::t('_COURSES', 'adminmanager'));
$_img_classlocations = Get::sprite('subs_location', Lang::t('_LOCATION', 'adminmanager'));

if ($permissions['assign_users']) $_columns[] = array('key' => 'users', 'label' => $_img_users, 'className' => 'img-cell', 'formatter' => 'AdminManagement.formatUsers');
if ($permissions['assign_courses']) $_columns[] = array('key' => 'courses', 'label' => $_img_courses, 'className' => 'img-cell', 'formatter' => 'AdminManagement.formatCourses');
if ($permissions['assign_courses']) $_columns[] = array('key' => 'classlocations', 'label' => $_img_classlocations, 'className' => 'img-cell', 'formatter' => 'AdminManagement.formatClasslocations');

$this->widget('table', array(
	'id'			=> 'admin_manager_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/adminmanager/getAdmin&',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'userid',
	'dir'			=> 'asc',
	'columns'		=> $_columns,
	'fields'		=> array('id_user', 'userid', 'firstname', 'lastname', 'idst_profile', 'user_profile', 'has_users', 'has_courses', 'has_classlocations'),
	'generateRequest' => 'AdminManagement.requestBuilder'
));

?>
</div>
<script type="text/javascript">
	
var AdminManagement = {

	oLangs: new LanguageManager(),
	filterText: "",
	noProfileIcon: "",

	init: function(oConfig) {
		if (oConfig.filterText) this.filterText = oConfig.filterText;
		if (oConfig.langs) this.oLangs.set(oConfig.langs);
		if (oConfig.noProfileIcon) this.noProfileIcon = oConfig.noProfileIcon;
	},

	formatUserProfile: function(elLiner, oRecord, oColumn, oData) {
		if (oData === false) {
			elLiner.innerHTML = AdminManagement.noProfileIcon;
		} else {
			elLiner.innerHTML = oData;
		}
	},

	formatUsers: function(elLiner, oRecord, oColumn, oData) {
		var url ='index.php?r=adm/adminmanager/users&amp;id_user='+oRecord.getData("id_user")+'&amp;load=1';
		var style = oRecord.getData("has_users") > 0 ? 'subs_users' : 'fd_notice';
		var title = AdminManagement.oLangs.get('_USERS_ASSOCIATION');
		elLiner.innerHTML = '<a href="'+url+'" class="ico-sprite '+style+'" title="'+title+'"><span>'+title+'</span></a>';
	},

	formatCourses: function(elLiner, oRecord, oColumn, oData) {
		var url ='index.php?r=adm/adminmanager/courses&amp;id_user='+oRecord.getData("id_user")+'&amp;load=1';
		var style = oRecord.getData("has_courses") > 0 ? 'subs_elem' : 'fd_notice';
		var title = AdminManagement.oLangs.get('_COURSES');
		elLiner.innerHTML = '<a href="'+url+'" class="ico-sprite '+style+'" title="'+title+'"><span>'+title+'</span></a>';
	},

	formatClasslocations: function(elLiner, oRecord, oColumn, oData) {
		var url ='index.php?r=adm/adminmanager/classlocations&amp;id_user='+oRecord.getData("id_user")+'&amp;load=1';
		var style = oRecord.getData("has_classlocations") > 0 ? 'subs_location' : 'fd_notice';
		var title = AdminManagement.oLangs.get('_LOCATION');
		elLiner.innerHTML = '<a href="'+url+'" class="ico-sprite '+style+'" title="'+title+'"><span>'+title+'</span></a>';
	},

	setFilter: function() {
		AdminManagement.filterText = this.value;
		DataTable_admin_manager_table.refresh();
	},

	resetFilter: function() {
		this.value = "";
		AdminManagement.filterText = "";
		DataTable_admin_manager_table.refresh();
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
			"&filter_text=" + AdminManagement.filterText;
	},


	asyncSubmitter: function (callback, newData) {
		var record = this.getRecord();
		var id_user = record.getData("id_user");
		var col = this.getColumn().key;
		var new_value = newData;
		var new_string = "";
		var old_value =  record.getData("id_profile");
		var old_string = record.getData("user_profile");

		var editorCallback = {
			success: function(o) {
				var r = YAHOO.lang.JSON.parse(o.responseText);
				if (r.success) {
					record.setData("idst_profile", new_value);
					if (r.new_string !== false) {
						new_string = r.new_string + "";
					} else {
						new_string = AdminManagement.noProfileIcon;
					}
					callback(true, new_string);
				} else {
					callback(false);
				}
			},
			failure: {}
		}

		var post = "&id_user=" + id_user + "&idst_profile=" + new_value + "&old_value=" + old_value;
		var url = "ajax.adm_server.php?r=adm/adminmanager/update_profile";
		YAHOO.util.Connect.asyncRequest("POST", url, editorCallback, post);
	}

}

AdminManagement.init({
	filterText: "<?php echo $filter_text; ?>",
	noProfileIcon: '<?php echo addslashes(Get::sprite('fd_notice', Lang::t('_NONE', 'adminrules'))); ?>',
	langs: {
		_NONE: "<?php echo Lang::t('_NONE', 'adminrules'); ?>",
		_USERS_ASSOCIATION: "<?php echo Lang::t('_ASSIGN_USERS', 'adminmanager'); ?>",
		_COURSES: "<?php echo Lang::t('_COURSES', 'adminmanager'); ?>",
		_LOCATION: "<?php echo Lang::t('_LOCATION', 'adminmanager'); ?>"
	}
});

</script>
