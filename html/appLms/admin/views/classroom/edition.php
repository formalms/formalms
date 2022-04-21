<?php Forma\lib\Get::title([
    'index.php?r=' . $base_link_course . '/show' => Lang::t('_COURSE', 'course'),
    Lang::t('_CLASSROOM', 'course') . ' : ' . $course_name,
]); ?>
<div class="std_block">
<?php

echo getBackUi('index.php?r=' . $base_link_course . '/show', Lang::t('_BACK', 'course'));

$_columns = [
    ['key' => 'code', 'label' => Lang::t('_CODE', 'course'), 'sortable' => true],
    ['key' => 'name', 'label' => Lang::t('_NAME', 'course'), 'sortable' => true],
    ['key' => 'status', 'label' => Lang::t('_STATUS', 'course'), 'sortable' => true, 'formatter' => 'statusFormatter'],
    ['key' => 'date_begin', 'label' => Lang::t('_DATE_BEGIN', 'course'), 'sortable' => true],
    ['key' => 'date_end', 'label' => Lang::t('_DATE_END', 'course'), 'sortable' => true],
    ['key' => 'classroom', 'label' => Lang::t('_CLASSROOM', 'course')],
    ['key' => 'students', 'label' => Lang::t('_STUDENTS', 'coursereport'), 'className' => 'img-cell'],
];

if ($permissions['add'] && $permissions['mod'] && $permissions['del']) {
    $_columns[] = ['key' => 'registro', 'label' => Forma\lib\Get::img('standard/date.png', Lang::t('_MOD', 'course')), 'className' => 'img-cell'];
}

if ($permissions['subscribe']) {
    $_columns[] = ['key' => 'subscription', 'label' => Forma\lib\Get::sprite('subs_users', Lang::t('_SUBSCRIPTION', 'course')), 'className' => 'img-cell'];
    $_columns[] = ['key' => 'presence', 'label' => Lang::t('_ATTENDANCE', 'course'), 'className' => 'img-cell'];
}

if ($permissions['mod']) {
    $_columns[] = ['key' => 'mod', 'label' => Forma\lib\Get::img('standard/edit.png', Lang::t('_MOD', 'course')), 'className' => 'img-cell'];
}

if ($permissions['del'] && !Forma\lib\Get::cfg('demo_mode')) {
    $_columns[] = ['key' => 'del', 'label' => Forma\lib\Get::img('standard/delete.png', Lang::t('_DEL', 'course')), 'formatter' => 'doceboDelete', 'className' => 'img-cell'];
}

$event = Events::trigger('core.course.edition.columns.listing', ['columns' => $_columns, 'fields' => ['id_date', 'code', 'name', 'status', 'date_begin', 'registro', 'date_end', 'classroom', 'students', 'num_subscribe', 'subscription', 'presence', 'mod', 'del']]);

$_params = [
    'id' => 'classroom_edition_table',
    'ajaxUrl' => 'ajax.adm_server.php?r=' . $base_link_classroom . '/getclassroomedition&id_course=' . $model->getIdCourse() . '&',
    'rowsPerPage' => Forma\lib\Get::sett('visuItem', 25),
    'startIndex' => 0,
    'results' => Forma\lib\Get::sett('visuItem', 25),
    'sort' => 'name',
    'dir' => 'asc',
    'columns' => $event['columns'],
    'fields' => $event['fields'],
    'show' => 'table',
    'editorSaveEvent' => '',
];

if ($permissions['add']) {
    $_params['rel_actions'] = '<a class="ico-wt-sprite subs_add" href="index.php?r=' . $base_link_classroom . '/addclassroom&amp;id_course=' . $model->getIdCourse() . '"><span>' . Lang::t('_ADD', 'subscribe') . '</span></a>';
}

$this->widget('table', $_params);

echo getBackUi('index.php?r=' . $base_link_course . '/show', Lang::t('_BACK', 'course'));

?>
</div>
<script type="text/javascript">
var StatusList = {
<?php
    $conds = [];
    $list = $this->model->getStatusForDropdown();
    foreach ($list as $id_status => $name_status) {
        $conds[] = 'status_' . $id_status . ': "' . str_replace('"', '\\' . '"', $name_status) . '"';
    }
    if (!empty($conds)) {
        echo implode(',' . "\n", $conds);
    }
?>
};

var statusFormatter = function(elLiner, oRecord, oColumn, oData) {
	var index = 'status_'+oData;
	elLiner.innerHTML = StatusList[index] || "";
}
</script>
