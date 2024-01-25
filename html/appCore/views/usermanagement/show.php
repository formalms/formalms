<?php echo getTitleArea(Lang::t('_ORG_CHART', 'organization_chart')); ?>
<div class="std_block">
<?php if (isset($result_message)) {
    echo $result_message;
} ?>
<div id="ui_feedback_box"></div>
<?php

$_other_links = '';
if ($permissions['approve_waiting_user'] && $num_waiting_users > 0) {
    $_other_links .= '<a class="ico-wt-sprite subs_wait" href="index.php?r=' . $this->link . '/show_waiting" '
        . ' title="' . Lang::t('_WAITING_USERS', 'admin_directory') . '">'
        . '<span>' . Lang::t('_WAITING_USERS', 'admin_directory') . ' (' . $num_waiting_users . ')</span>'
        . '</a>';
}

if ($permissions['view_deleted_user'] && FormaLms\lib\Get::sett('register_deleted_user', 'off') == 'on') {
    $_other_links .= '<a class="ico-wt-sprite subs_unassoc" href="index.php?r=' . $this->link . '/show_deleted" '
        . ' title="' . Lang::t('_DELETED_USER_LIST', 'profile') . '" id="show_deleted_users">'
        . '<span>' . Lang::t('_DELETED_USER_LIST', 'profile') . ' (' . $num_deleted_users . ')</span>'
        . '</a>';
}

/*
 * Tree
 */
if ($permissions['view_org']) {
    $languages = [
        '_ROOT' => FormaLms\lib\Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', 'organization_chart')),
        '_YES' => Lang::t('_CONFIRM', 'organization_chart'),
        '_NO' => Lang::t('_UNDO', 'organization_chart'),
        '_LOADING' => Lang::t('_LOADING', 'standard'),
        '_NEW_FOLDER_NAME' => Lang::t('_ORGCHART_ADDNODE', 'organization_chart'),
        '_AREYOUSURE' => Lang::t('_AREYOUSURE', 'organization_chart'),
        '_MOVE_ORGBRANCH' => Lang::t('_MOVE_ORGBRANCH', 'organization_chart'),
        '_NAME' => Lang::t('_NAME', 'standard'),
        '_MOD' => Lang::t('_MOD', 'standard'),
        '_DEL' => Lang::t('_DEL', 'standard'),
    ];

    $tree_rel_action = ($permissions['add_org'] ?
        '<a class="ico-wt-sprite subs_add" id="add_org_folder" href="' . ($this->model->isFolderEnabled($selected_orgchart, Docebo::user()->getIdSt()) ? 'ajax.adm_server.php?r=' . $this->link . '/addfolder_dialog&id=' . (int) $selected_orgchart . '" ' : '" style="visibility:hidden"')
        . ' title="' . Lang::t('_ORGCHART_ADDNODE', 'organization_chart') . '">'
        . '<span>' . Lang::t('_ORGCHART_ADDNODE', 'organization_chart') . '</span>'
        . '</a>' : '')
        . ($permissions['add_user'] ?
        '<a class="ico-wt-sprite subs_import" id="import_users_action" href="' . ($this->model->isFolderEnabled($selected_orgchart, Docebo::user()->getIdSt()) ? 'index.php?r=' . $this->link . '/importusers&id=' . (int) $selected_orgchart . '" ' : '" style="visibility:hidden"')
        . ' title="' . Lang::t('_ORG_CHART_IMPORT_USERS', 'organization_chart') . '">'
        . '<span>' . Lang::t('_ORG_CHART_IMPORT_USERS', 'organization_chart') . '</span>'
        . '</a>' : '');

    $this->widget('tree', [
        'id' => 'usertree',
        'ajaxUrl' => 'ajax.adm_server.php?r=' . $this->link . '/gettreedata',
        'treeClass' => 'OrgFolderTree',
        'treeFile' => FormaLms\lib\Get::rel_path('adm') . '/views/usermanagement/orgchartfoldertree.js',
        'languages' => $languages,
        'initialSelectedNode' => (int) $selected_orgchart,
        'rootActions' => $root_node_actions,
        'show' => 'tree',
        'dragDrop' => true,
        'rel_action' => $tree_rel_action . $_other_links,
    ]);

    /*
     * Add folder dialog
     */
    if ($permissions['add_org']) {
        $this->widget('dialog', [
            'id' => 'add_folder_dialog',
            'dynamicContent' => true,
            'ajaxUrl' => 'this.href',
            'dynamicAjaxUrl' => true,
            'callback' => 'UserManagement.addFolderCallback',
            'callEvents' => [
                ['caller' => 'add_org_folder', 'event' => 'click'],
            ],
        ]);
    }
} else {
    echo $_other_links;
}

if ($permissions['view_user']) {
    // Search form?>
<div class="quick_search_form">
	<div class="common_options">
		<?php
        echo Form::getInputCheckbox('flatview', 'flatview', '1', ($show_descendants ? true : false), '')
            . ' <label class="label_normal" for="flatview">' . Lang::t('_DIRECTORY_FILTER_FLATMODE', 'admin_directory') . '</label>'
            . '&nbsp;&nbsp;&nbsp;&nbsp;'
            . Form::getInputCheckbox('show_suspended', 'show_suspended', '1', ($show_suspended ? true : false), '')
            . ' <label class="label_normal" for="show_suspended">' . Lang::t('_SHOW_SUSPENDED', 'organization_chart') . '</label>'; ?>
	</div>
	<div>
		<a id="usermanagement_filter_selector" href="javascript:;" class="advanced_search">
			<?php echo Lang::t('_ADVANCED_SEARCH', 'standard'); ?>
		</a>
		<div class="simple_search_box" id="usermanagement_simple_filter_options" style="display: block;">
			<?php
                echo Form::getInputTextfield('search_t', 'filter_text', 'filter_text', $filter_text, '', 255, '');
    echo Form::getButton('filter_set', 'filter_set', Lang::t('_SEARCH', 'standard'), 'search_b');
    echo Form::getButton('filter_reset', 'filter_reset', Lang::t('_RESET', 'standard'), 'reset_b'); ?>
		</div>
		<div class="overlay_menu advanced_search_box" id="usermanagement_advanced_filter_options">
        
			<?php
                $dyn_data = $dynamic_filter->get(true, true);
    echo $dyn_data['html'];
    echo $dyn_data['js'];
    echo Form::openButtonSpace();
    echo Form::getButton('apply_dyn_filter', 'apply_dyn_filter', Lang::t('_SEARCH', 'admin_directory'));
    echo '&nbsp;';
    echo Form::getButton('reset_dyn_filter', 'reset_dyn_filter', Lang::t('_RESET', 'admin_directory'));
    echo Form::closeButtonSpace(); ?>
		</div>
	</div>
</div>
<?php
/*
 * Table
 */
$dyn_labels = [];
    $dyn_filter = [];
    for ($i = 0; $i < $num_var_fields; ++$i) {
        $label = '<select id="_dyn_field_selector_' . $i . '" name="_dyn_field_selector[' . $i . ']">';
        foreach ($fieldlist as $key => $value) {
            $label .= '<option value="' . $key . '"'
            . ($selected[$i] == $key ? ' selected="selected"' : '')
            . '>' . $value . '</option>';
        }
        $label .= '</select>';
        $label .= '<a id="_dyn_field_sort_' . $i . '" href="javascript:;">';
        $label .= '<img src="' . FormaLms\lib\Get::tmpl_path() . 'images/standard/sort.png" title="' . Lang::t('_SORT', 'standard') . '" alt="' . Lang::t('_SORT', 'standard') . '" />';
        $label .= '</a>';
        $dyn_filter[$i] = $selected[$i];
        $dyn_labels[$i] = $label;
    }

    //set columns
    $icon_profile = '<span class="ico-sprite subs_view" title="' . Lang::t('_DETAILS', 'admin_directory') . '"><span>' . Lang::t('_DETAILS', 'admin_directory') . '</span></span>';
    $icon_orgbranch = '<span class="ico-sprite subs_unassoc" title="' . Lang::t('_REMOVE_FROM_NODE', 'organization_chart') . '"><span>' . Lang::t('_REMOVE_FROM_NODE', 'organization_chart') . '</span></span>';
    $icon_suspend = '<span class="ico-sprite subs_unlocked" title="' . Lang::t('_SUSPEND', 'standard') . '"><span>' . Lang::t('_SUSPEND', 'standard') . '</span></span>';
    $icon_mod = '<span class="ico-sprite subs_mod" title="' . Lang::t('_MOD', 'standard') . '"><span>' . Lang::t('_MOD', 'standard') . '</span></span>';
    $icon_del = '<span class="ico-sprite subs_del" title="' . Lang::t('_DEL', 'standard') . '"><span>' . Lang::t('_DEL', 'standard') . '</span></span>';

    $columns_arr = [];
    $columns_arr[] = ['key' => 'userid', 'label' => Lang::t('_USERNAME'), 'sortable' => true, 'formatter' => 'UserManagement.labelFormatter'];
    $columns_arr[] = ['key' => 'lastname', 'label' => Lang::t('_LASTNAME'), 'sortable' => true, 'formatter' => 'UserManagement.labelFormatter'];
    $columns_arr[] = ['key' => 'firstname', 'label' => Lang::t('_FIRSTNAME'), 'sortable' => true, 'formatter' => 'UserManagement.labelFormatter'];
    for ($i = 0; $i < $num_var_fields; ++$i) {
        $columns_arr[] = ['key' => '_dyn_field_' . $i, 'label' => $dyn_labels[$i]];
    }
    $columns_arr[] = ['key' => 'profile', 'label' => $icon_profile, 'formatter' => 'UserManagement.profileFormatter', 'className' => 'img-cell'];
    if ($permissions['associate_user']) {
        $columns_arr[] = ['key' => 'unassoc', 'label' => $icon_orgbranch, 'formatter' => 'UserManagement.orgbranchFormatter', 'className' => 'img-cell'];
    }
    if ($permissions['mod_user']) {
        $columns_arr[] = ['key' => 'valid', 'label' => $icon_suspend, 'formatter' => 'UserManagement.suspendFormatter', 'className' => 'img-cell'];
    }
    if ($permissions['mod_user']) {
        $columns_arr[] = ['key' => 'mod', 'label' => $icon_mod, 'formatter' => 'doceboModify', 'className' => 'img-cell'];
    }
    if ($permissions['del_user'] && !FormaLms\lib\Get::cfg('demo_mode')) {
        $columns_arr[] = ['key' => 'del', 'label' => $icon_del, 'formatter' => 'doceboDelete', 'className' => 'img-cell'];
    }

    // Releated actions

    if ($permissions['add_user']) {
        $languages = [
        '_ROOT' => FormaLms\lib\Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', 'organization_chart')),
        '_LOADING' => Lang::t('_LOADING', 'standard'),
    ];

        $this->widget('tree', [
        'id' => 'createuser_orgchart_tree',
        'ajaxUrl' => 'ajax.adm_server.php?r=' . $this->link . '/gettreedata_create',
        'treeClass' => 'DialogOrgFolderTree',
        'treeFile' => FormaLms\lib\Get::rel_path('adm') . '/views/usermanagement/orgchartfoldertree.js',
        'languages' => $languages,
        'initialSelectedNode' => 0,
        'show' => 'tree',
        'useCheckboxes' => 'true',
        'initialSelectorData' => [0],
        'setSelectedNodeOnServer' => false,
        'hiddenSelection' => 'orgchart_hidden_selection',
        'runtime' => true,
    ]);
    }

    $rel_action = ($permissions['add_user'] ? '<a class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=' . $this->link . '/create">'
    . '<span>' . Lang::t('_NEW_USER', 'admin_directory') . '</span>'
    . '</a>' : '');

    $_show_more = ($permissions['mod_user'] || ($permissions['del_user'] && !FormaLms\lib\Get::cfg('demo_mode')) || $permissions['associate_user']);

    $rel_action_over = ' '
    . '<button id="ma_over" name="ma"></button> '
    . '<span class="ma_selected_users">'
    . '<b id="num_users_selected_top">' . (int) (isset($num_users_selected) ? $num_users_selected : '0') . '</b> ' . Lang::t('_SELECTED', 'admin_directory')
    . '</span>';

    $rel_action_bottom = ' '
    . '<button id="ma_bottom" name="ma"></button> '
    . '<span class="ma_selected_users">'
    . '<b id="num_users_selected_bottom">' . (int) (isset($num_users_selected) ? $num_users_selected : '0') . '</b> ' . Lang::t('_SELECTED', 'admin_directory')
    . '</span>';

    $arr_fields = ['id', 'userid', 'firstname', 'lastname', 'profile', 'unassoc', 'valid', 'mod', 'del'];
    for ($i = 0; $i < $num_var_fields; ++$i) {
        $arr_fields[] = '_dyn_field_' . $i;
    }

    //render table
    $this->widget('table', [
    'id' => 'usertable',
    'ajaxUrl' => 'ajax.adm_server.php?r=' . $this->link . '/gettabledata&select_node=1',
    'sort' => 'userid',
    'columns' => $columns_arr,
    'fields' => $arr_fields,
    'stdSelection' => true,
    'stdSelectionField' => '_checked',
    'selectAllAdditionalFilter' => 'UserManagement.selectAllAdditionalFilter()',
    'rel_actions' => [$rel_action . $rel_action_over, $rel_action . $rel_action_bottom],
    'delDisplayField' => 'userid',
    'stdDeleteCallbackEvent' => 'UserManagement.updateDeletedUsersTotal',
    'generateRequest' => 'UserManagement.requestBuilder',
    'events' => [
        'initEvent' => 'UserManagement.initEvent',
        'beforeRenderEvent' => 'UserManagement.beforeRenderEvent',
        'postRenderEvent' => 'UserManagement.postRenderEvent',
    ],
]);

    //invisible form for export action
    echo Form::openForm('csv_form', 'index.php?r=' . $this->link . '/csvexport');
    echo Form::getHidden('csv_input', 'users', '');
    echo Form::closeForm();
} //end if check view permission for users

?>
</div>
<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function() {
	YAHOO.util.Event.addListener("usermanagement_filter_selector", "click", function(e) {
		var smp_menu = YAHOO.util.Dom.get("usermanagement_simple_filter_options");
		var adv_menu = YAHOO.util.Dom.get('usermanagement_advanced_filter_options');
		if (smp_menu.style.display != 'block') {
			this.innerHTML = '<?php echo Lang::t('_ADVANCED_SEARCH', 'standard'); ?>';
			smp_menu.style.display = 'block';
			adv_menu.style.display = 'none';
			UserManagement.useAdvancedFilter = false;
		} else {
			this.innerHTML = '<?php echo Lang::t('_BASIC_SEARCH', 'standard'); ?>';
			smp_menu.style.display = 'none';
			adv_menu.style.display = 'block';
			UserManagement.useAdvancedFilter = true;
		}
	});
});

UserManagement.init({
	baseUrl: "<?php echo $this->link; ?>",
	templatePath: "<?php echo FormaLms\lib\Get::tmpl_path(); ?>",
	selectedOrgBranch: <?php echo (int) $selected_orgchart; ?>,
	showDescendants: <?php echo $show_descendants ? 'true' : 'false'; ?>,
	showSuspended: <?php echo $show_suspended ? 'true' : 'false'; ?>,
	filterText: "<?php echo $filter_text; ?>",
	useAdvancedFilter: false,
	dynSelection: {},
	fieldList: <?php echo $fieldlist_js; ?>,
	numVarFields: <?php echo $num_var_fields; ?>,
	perms: {
		mod_user: <?php echo $permissions['mod_user'] ? 'true' : 'false'; ?>,
		del_user: <?php echo $permissions['del_user'] && !FormaLms\lib\Get::cfg('demo_mode') ? 'true' : 'false'; ?>,
		associate_user: <?php echo $permissions['associate_user'] ? 'true' : 'false'; ?>
	},
	langs: {
		_AREYOUSURE: "<?php echo Lang::t('_AREYOUSURE', 'standard'); ?>",
        _MOVE_ORGBRANCH: "<?php echo Lang::t('_MOVE_ORGBRANCH', 'organization_chart'); ?>",
		_EXPORT_CSV: "<?php echo Lang::t('_EXPORT_CSV', 'admin_directory'); ?>",
		_MOD: "<?php echo Lang::t('_MOD', 'standard'); ?>",
		_DEL: "<?php echo Lang::t('_DEL', 'standard'); ?>",
		_GENERATE_PASSWORD: "<?php echo Lang::t('_GENERATE_PASSWORD', 'user_managment'); ?>",
		_MORE_ACTIONS: "<?php echo Lang::t('_MORE_ACTIONS', 'admin_directory'); ?>",
		_EMPTY_SELECTION: "<?php echo Lang::t('_EMPTY_SELECTION', 'admin_directory'); ?>",
		_DIRECTORY_MEMBERTYPETREE: "<?php echo Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory'); ?>",
		_DETAILS: "<?php echo Lang::t('_DETAILS', 'standard'); ?>",
		_SORT: "<?php echo Lang::t('_SORT', 'standard'); ?>",
		_SUSPEND: "<?php echo Lang::t('_SUSPEND', 'admin_directory'); ?>",
		_REMOVE_FROM_NODE: "<?php echo Lang::t('_REMOVE_FROM_NODE', 'admin_directory'); ?>",
		_REACTIVATE: "<?php echo Lang::t('_REACTIVATE', 'admin_directory'); ?>",
		_USERS: "<?php echo Lang::t('_USERS', 'standard'); ?>"
	}
});

</script>
