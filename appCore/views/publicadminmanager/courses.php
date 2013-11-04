<?php
$array_title = array(
	'index.php?r=adm/publicadminmanager/show' => Lang::t('_PUBLIC_ADMIN_MANAGER', 'menu'),
	Lang::t('_COURSES', 'adminmanager') . ' : ' . $model->getAdminFullname($id_user)
);
echo getTitleArea($array_title)
	.'<div class="std_block">'
	.Form::openForm('courses_association_form', 'index.php?r=adm/publicadminmanager/courses&id_user=' . $id_user)
	.Form::openElementSpace()
	.'<div>'
	.'<input id="all_courses" name="all_courses" type="radio" value="1" ' . ($all_courses == 1 ? 'checked="checked"' : '') . ' />'
	.' <label for="all_courses">' . Lang::t('_ALL_COURSES', 'adminmanager') . '</label>'
	.'<input id="cat_courses" name="all_courses" type="radio" value="-1" ' . ($all_courses == -1 ? 'checked="checked"' : '') . ' />'
	.' <label for="cat_courses">' . Lang::t('_ALL_COURSES_IN_CATALOGUE', 'adminmanager') . '</label>'
	.' <input id="sel_courses" name="all_courses" type="radio" value="0" ' . ($all_courses == 0 ? 'checked="checked"' : '') . ' />'
	.' <label for="sel_courses">' . Lang::t('_SELECT', 'adminmanager') . '</label><br /><br />'
	.'</div>'
	.Form::closeElementSpace()
	.Form::openElementSpace()
	.'<div id="course_selector" style="display:' . ($all_courses == 1 || $all_courses == -1 ? 'none' : 'block') . ';">';

echo $course_selector->loadSelector(true, true);

echo '</div>'
	.Form::closeElementSpace()
	.Form::openButtonSpace()
	.Form::getButton('save', 'save', Lang::t('_SAVE', 'adminmanager'))
	.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'adminmanager'))
	.Form::closeButtonSpace()
	.Form::closeForm()
	.'</div>';
?>
<script type="text/javascript">
	function selectorEvent(e) {
		var div = YAHOO.util.Dom.get('course_selector');
		var all = YAHOO.util.Dom.get('all_courses');
		var cat = YAHOO.util.Dom.get('cat_courses');
		var sel = YAHOO.util.Dom.get('sel_courses');

		if(all.checked)
			div.style.display = 'none';
		if(cat.checked)
			div.style.display = 'none';
		if(sel.checked)
			div.style.display = 'block';
	}
	YAHOO.util.Event.addListener(['all_courses', 'sel_courses', 'cat_courses'], 'click', selectorEvent);
</script>
