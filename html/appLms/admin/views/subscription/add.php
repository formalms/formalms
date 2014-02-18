<?php Get::title(array(
	'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSES', 'admin_course_managment'),
	'index.php?r='.$this->link.'/show&id_course='.(int)$id_course.($id_edition ? '&id_edition='.(int)$id_edition : '').($id_date ? '&id_date='.(int)$id_date : '') => Lang::t('_SUBSCRIBE', 'subscribe').' : '.$course_name,
	Lang::t('_ADD', 'subscribe')
)); ?>
<div class="std_block">
<?php
echo Form::openForm('main_selector_form', 'index.php?r='.$this->link.'/add&amp;id_course='.$model->getIdCourse().'&amp;id_edition='.$model->getIdEdition().'&amp;id_date='.$model->getIdDate().'&amp;jump=1');
echo Lang::t('_CHOOSE_SUBSCRIBE', 'subscribe').': <b>'.(trim($course_info['code']) ? '['.trim($course_info['code']).'] ' : '').$course_info['name'].'</b><br/><br/>';
?>
<a id="advanced_options" class="advanced_search" href="javascript:;"><?php echo Lang::t("_MORE_ACTIONS", 'standard'); ?></a>
<div id="advanced_subs_options" class="advanced_search_options" style="display: none;">
<?php
echo Form::getDropdown( Lang::t('_LEVEL', 'subscribe'),
	'select_level_mode',
	'select_level_mode',
	array( 'manual' => Lang::t('_MANUAL', 'subscribe'), 'students' => Lang::t('_LEVEL_3', 'levels') ),
	'manual' );
echo Form::getDatefield( Lang::t('_DATE_BEGIN_VALIDITY', 'subscribe'),
	'set_date_begin_validity',
	'set_date_begin_validity',
	'', false, false, '', '',
	Form::getInputCheckbox('sel_date_begin_validity', 'sel_date_begin_validity', 1, false, '') );
echo Form::getDatefield( Lang::t('_DATE_EXPIRE_VALIDITY', 'subscribe'),
	'set_date_expire_validity',
	'set_date_expire_validity',
	'', false, false, '', '',
	Form::getInputCheckbox('sel_date_expire_validity', 'sel_date_expire_validity', 1, false, '') );
?>
</div>
<script type="text/javascript">
var E = YAHOO.util.Event;
E.onDOMReady(function() {

	E.addListener("set_date_begin_validity", "change", function() {});
	E.addListener("set_date_expire_validity", "change", function() {});

	E.addListener("advanced_options", "click", function(e) {
		var el = YAHOO.util.Dom.get("advanced_subs_options");
		if (el.style.display != 'block') {
			el.style.display = 'block'
		} else {
			el.style.display = 'none'
		}
	});

});
</script>
<br/>
<br/>
<?php
$this->widget('userselector', array(
	'id' => 'main_selector',
	'show_user_selector' => true,
	'show_group_selector' => true,
	'show_orgchart_selector' => true,
	'show_fncrole_selector' => true,
	'initial_selection' => $user_alredy_subscribed,
	'admin_filter' => true
));

echo Form::openButtonSpace()
	.Form::getInputCheckbox( 'send_alert', 'send_alert', 1, $send_alert, false )
	.' <label for="send_alert">'.Lang::t('_SEND_ALERT', 'subscribe').'</label>&nbsp;&nbsp;&nbsp;&nbsp;'
	.Form::getButton('okselector', 'okselector', Lang::t('_NEXT', 'standard'))
	.Form::getButton('cancelselector', 'cancelselector', Lang::t('_UNDO', 'standard'))
	.Form::closeButtonSpace();

echo Form::closeForm();

?>
</div>