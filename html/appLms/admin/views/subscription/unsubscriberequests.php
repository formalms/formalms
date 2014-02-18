<?php
echo getTitleArea(array(
	'index.php?r=alms/course/show' => Lang::t('_COURSES', 'admin_courses_managment'),
	Lang::t('_UNSUBSCRIBE_REQUESTS', 'course')
));
?>
<div class="std_block">
<?php echo getBackUi('index.php?r=alms/course/show', Lang::t('_BACK', 'standard')); ?>
<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="waitingusers_simple_filter_options">
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
	</div>
</div>
<?php


//$icon_details = '<span class="ico-sprite subs_view"><span>'.Lang::t('_DETAILS', 'standard').'</span></span>';
$icon_confirm = '<span class="ico-sprite subs_unassoc"><span>'.Lang::t('_CONFIRM', 'standard').'</span></span>';
$icon_delete = '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>';

$rel_action_over = '<a class="ico-wt-sprite subs_unassoc" id="confirm_multi_over" href="ajax.adm_server.php?r='. $this->link.'/accept_unsubscribe_request_multi">'
	.'<span>'.Lang::t('_CONFIRM', 'admin_directory').'</span>'
	.'</a>'
	.'<a class="ico-wt-sprite subs_del" id="delete_multi_over" href="ajax.adm_server.php?r='. $this->link.'/deny_unsubscribe_request_multi">'
	.'<span>'.Lang::t('_DEL_SELECTED', 'admin_directory').'</span>'
	.'</a>'
	.'<span class="ma_selected_users">'
	.'<b id="num_subs_selected_top">'.(int)(isset($num_subs_selected) ? $num_subs_selected : '0').'</b> '.Lang::t('_SELECTED', 'admin_directory')
	.'</span>';

$rel_action_bottom = '<a class="ico-wt-sprite subs_unassoc" id="confirm_multi_bottom" href="ajax.adm_server.php?r='. $this->link.'/accept_unsubscribe_request_multi">'
	.'<span>'.Lang::t('_CONFIRM', 'admin_directory').'</span>'
	.'</a>'
	.'<a class="ico-wt-sprite subs_del" id="delete_multi_bottom" href="ajax.adm_server.php?r='. $this->link.'/deny_unsubscribe_request_multi">'
	.'<span>'.Lang::t('_DEL_SELECTED', 'admin_directory').'</span>'
	.'</a>'
	.'<span class="ma_selected_users">'
	.'<b id="num_subs_selected_bottom">'.(int)(isset($num_subs_selected) ? $num_subs_selected : '0').'</b> '.Lang::t('_SELECTED', 'admin_directory')
	.'</span>';

$params = array(
	'id' => 'unsubscriberequests_table',
	'ajaxUrl' => 'ajax.adm_server.php?r='. $this->link.'/getunsubscribetabledata',
	'rowsPerPage' => Get::sett('visuItem', 25),
	'startIndex' => 0,
	'results' => Get::sett('visuItem', 25),
	'sort' => 'userid',
	'dir' => 'desc',
	'columns' => array(
			array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'sortable' => true, 'formatter' => 'UnsubscribeRequests.labelFormatter'),
			array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'sortable' => true, 'formatter' => 'UnsubscribeRequests.labelFormatter'),
			array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'sortable' => true, 'formatter' => 'UnsubscribeRequests.labelFormatter'),
			array('key' => 'course_code', 'label' => Lang::t('_CODE', 'standard'), 'sortable' => true, 'formatter' => 'UnsubscribeRequests.labelFormatter'),
			array('key' => 'course_name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true, 'formatter' => 'UnsubscribeRequests.labelFormatter'),
			array('key' => 'request_date', 'label' => Lang::t('_DATE', 'admin_directory'), 'sortable' => true),
			array('key' => 'confirm', 'label' => $icon_confirm, 'formatter' => 'UnsubscribeRequests.confirmFormatter', 'className' => 'img-cell'),
			array('key' => 'del', 'label' => $icon_delete, 'formatter' => 'doceboDelete', 'className' => 'img-cell')
		),
	'fields' => array('id', 'id_user', 'res_id', 'userid', 'firstname', 'lastname', 'email', 'course_name', 'course_code', 'request_date', 'r_type', 'del'),
	'generateRequest' => 'UnsubscribeRequests.requestBuilder',
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'stdSelection' => true,
	'delDisplayField' => 'userid',
	'selectAllAdditionalFilter' => 'UnsubscribeRequests.selectAllAdditionalFilter()',
	'events' => array(
			'initEvent' => 'UnsubscribeRequests.initEvent',
			'beforeRenderEvent' => 'UnsubscribeRequests.beforeRenderEvent',
			'postRenderEvent' => 'UnsubscribeRequests.postRenderEvent'
		)
);

$this->widget('table', $params);

echo getBackUi('index.php?r=alms/course/show', Lang::t('_BACK', 'standard'));

?>
</div>
<script type="text/javascript">
UnsubscribeRequests.init({
	filterText: "<?php echo $filter_text; ?>",
	link: "<?php echo $this->link; ?>",
	langs: {
		_AREYOUSURE: "<?php echo Lang::t('_AREYOUSURE', 'standard'); ?>",
		_CONFIRM: "<?php echo Lang::t('_CONFIRM', 'admin_directory'); ?>",
		_DEL: "><?php echo Lang::t('_DEL', 'standard'); ?>",
		_DETAILS: "<?php echo Lang::t('_DETAILS', 'standard'); ?>",
		_EMPTY_SELECTION: "<?php echo Lang::t('_EMPTY_SELECTION', 'admin_directory'); ?>",
		_USERS: "<?php echo Lang::t('_USERS', 'standard'); ?>"
	},
	link: '<?php echo $this->link; ?>'
});
</script>