<script type="text/javascript">
WaitingManagement.init({
	filterText: "<?php echo $filter_text; ?>",
	langs: {
		_AREYOUSURE: "<?php echo Lang::t('_AREYOUSURE', 'standard'); ?>",
		_CONFIRM: "<?php echo Lang::t('_CONFIRM', 'admin_directory'); ?>",
        _LINK_NOT_CONFIRMED: "<?php echo Lang::t('_LINK_NOT_CONFIRMED', 'admin_directory'); ?>",
        _LINK_CONFIRMED: "<?php echo Lang::t('_LINK_CONFIRMED', 'admin_directory'); ?>",
		_DEL: "><?php echo Lang::t('_DEL', 'standard'); ?>",
		_DETAILS: "<?php echo Lang::t('_DETAILS', 'standard'); ?>",
		_EMPTY_SELECTION: "<?php echo Lang::t('_EMPTY_SELECTION', 'admin_directory'); ?>",
		_USERS: "<?php echo Lang::t('_USERS', 'standard'); ?>"
	},
	link: '<?php echo $this->link; ?>'
});
</script>
<?php
echo getTitleArea([
    'index.php?r=' . $this->link . '/show' => Lang::t('_ORGCHART', 'directory'),
    Lang::t('_WAITING_USERS', 'admin_directory'),
]);
?>
<div class="std_block">
<?php echo getBackUi('index.php?r=' . $this->link . '/show', Lang::t('_BACK', 'standard')); ?>
<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="waitingusers_simple_filter_options">
			<?php
                echo Form::getInputTextfield('search_t', 'filter_text', 'filter_text', $filter_text, '', 255, '');
                echo Form::getButton('filter_set', 'filter_set', Lang::t('_SEARCH', 'standard'), 'search_b');
                echo Form::getButton('filter_reset', 'filter_reset', Lang::t('_RESET', 'standard'), 'reset_b');
            ?>
		</div>
	</div>
</div>
<?php

/*$this->widget('dialog', array(

));*/

$icon_details = '<span class="ico-sprite subs_view"><span>' . Lang::t('_DETAILS', 'standard') . '</span></span>';
$icon_confirm = '<span class="ico-sprite subs_actv"><span>' . Lang::t('_CONFIRM', 'standard') . '</span></span>';
$icon_delete = '<span class="ico-sprite subs_del"><span>' . Lang::t('_DEL', 'standard') . '</span></span>';

$rel_action_over = '<a class="ico-wt-sprite subs_actv" id="confirm_multi_over" href="ajax.adm_server.php?r=' . $this->link . '/confirm_waiting">'
    . '<span>' . Lang::t('_CONFIRM', 'admin_directory') . '</span>'
    . '</a>'
    . '<a class="ico-wt-sprite subs_del" id="delete_multi_over" href="ajax.adm_server.php?r=' . $this->link . '/delete_waiting">'
    . '<span>' . Lang::t('_DEL_SELECTED', 'admin_directory') . '</span>'
    . '</a>'
    . '<span class="ma_selected_users">'
    . '<b id="num_users_selected_top">' . (int) (isset($num_users_selected) ? $num_users_selected : '0') . '</b> ' . Lang::t('_SELECTED', 'admin_directory')
    . '</span>';

$rel_action_bottom = '<a class="ico-wt-sprite subs_actv" id="confirm_multi_bottom" href="ajax.adm_server.php?r=' . $this->link . '/confirm_waiting">'
    . '<span>' . Lang::t('_CONFIRM', 'admin_directory') . '</span>'
    . '</a>'
    . '<a class="ico-wt-sprite subs_del" id="delete_multi_bottom" href="ajax.adm_server.php?r=' . $this->link . '/delete_waiting">'
    . '<span>' . Lang::t('_DEL_SELECTED', 'admin_directory') . '</span>'
    . '</a>'
    . '<span class="ma_selected_users">'
    . '<b id="num_users_selected_bottom">' . (int) (isset($num_users_selected) ? $num_users_selected : '0') . '</b> ' . Lang::t('_SELECTED', 'admin_directory')
    . '</span>';

$params = [
    'id' => 'waitingtable',
    'ajaxUrl' => 'ajax.adm_server.php?r=' . $this->link . '/getwaitinguserstabledata',
    'rowsPerPage' => FormaLms\lib\Get::sett('visuItem', 25),
    'startIndex' => 0,
    'results' => FormaLms\lib\Get::sett('visuItem', 25),
    'sort' => 'userid',
    'dir' => 'desc',
    'columns' => [
            ['key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'sortable' => true, 'formatter' => 'WaitingManagement.labelFormatter'],
            ['key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'sortable' => true, 'formatter' => 'WaitingManagement.labelFormatter'],
            ['key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'sortable' => true, 'formatter' => 'WaitingManagement.labelFormatter'],
            ['key' => 'email', 'label' => Lang::t('_EMAIL', 'standard'), 'sortable' => true, 'formatter' => 'WaitingManagement.labelFormatter'],
        ['key' => 'confirmed', 'label' => Lang::t('_STATUS', 'standard'), 'sortable' => true, 'formatter' => 'WaitingManagement.statusFormatter'],
            ['key' => 'insert_date', 'label' => Lang::t('_DATE', 'admin_directory'), 'sortable' => true],
            ['key' => 'inserted_by', 'label' => ucfirst(Lang::t('_BY', 'admin_directory')), 'sortable' => true],
            ['key' => 'details', 'label' => $icon_details, 'formatter' => 'WaitingManagement.detailsFormatter', 'className' => 'img-cell'],
            ['key' => 'confirm', 'label' => $icon_confirm, 'formatter' => 'WaitingManagement.confirmFormatter', 'className' => 'img-cell'],
            ['key' => 'del', 'label' => $icon_delete, 'formatter' => 'doceboDelete', 'className' => 'img-cell'],
    ],
    'fields' => ['id', 'userid', 'firstname', 'lastname', 'email', 'confirmed', 'insert_date', 'inserted_by', 'del'],
    'generateRequest' => 'WaitingManagement.requestBuilder',
    'rel_actions' => [$rel_action_over, $rel_action_bottom],
    'stdSelection' => true,
    'delDisplayField' => 'userid',
    'selectAllAdditionalFilter' => 'WaitingManagement.selectAllAdditionalFilter()',
    'events' => [
            'initEvent' => 'WaitingManagement.initEvent',
            'beforeRenderEvent' => 'WaitingManagement.beforeRenderEvent',
            'postRenderEvent' => 'WaitingManagement.postRenderEvent',
    ],
];

$this->widget('table', $params);

?>
<?php echo getBackUi('index.php?r=' . $this->link . '/show', Lang::t('_BACK', 'standard')); ?>
</div>
