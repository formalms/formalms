<?php
$title = ['index.php?r=' . $base_link_course . '/show' => Lang::t('_COURSE', 'course'),
                Lang::t('_EDITIONS', 'course'), ];

echo getTitleArea($title);
?>
<div class="std_block">
<?php echo $back_link; ?>
<?php

$_columns = [
    ['key' => 'code', 'label' => Lang::t('_CODE', 'course'), 'sortable' => true],
    ['key' => 'name', 'label' => Lang::t('_NAME', 'course'), 'sortable' => true],
    ['key' => 'status', 'label' => Lang::t('_STATUS', 'course'), 'sortable' => true, 'formatter' => 'statusFormatter'],
    ['key' => 'date_begin', 'label' => Lang::t('_DATE_BEGIN', 'course'), 'sortable' => true],
    ['key' => 'date_end', 'label' => Lang::t('_DATE_END', 'course'), 'sortable' => true],
    ['key' => 'students', 'label' => Lang::t('_STUDENTS', 'coursereport'), 'className' => 'img-cell'],
];

if ($permissions['subscribe']) {
    $_columns[] = ['key' => 'subscription', 'label' => FormaLms\lib\Get::sprite('subs_users', Lang::t('_SUBSCRIPTION', 'course')), 'className' => 'img-cell'];
}

if ($permissions['mod']) {
    $_columns[] = ['key' => 'edit', 'label' => FormaLms\lib\Get::img('standard/edit.png', Lang::t('_MOD', 'course')), 'className' => 'img-cell'];
}

if ($permissions['del'] && !FormaLms\lib\Get::cfg('demo_mode')) {
    $_columns[] = ['key' => 'del', 'label' => FormaLms\lib\Get::img('standard/delete.png', Lang::t('_DEL', 'course')), 'formatter' => 'doceboDelete', 'className' => 'img-cell'];
}

$_params = [
    'id' => 'edition_table',
    'ajaxUrl' => 'ajax.adm_server.php?r=' . $base_link_edition . '/geteditionlist&id_course=' . $model->getIdCourse(),
    'rowsPerPage' => FormaLms\lib\Get::sett('visuItem', 25),
    'startIndex' => 0,
    'results' => FormaLms\lib\Get::sett('visuItem', 25),
    'sort' => 'name',
    'dir' => 'asc',
    'columns' => $_columns,
    'fields' => ['id_edition', 'code', 'name', 'status', 'date_begin', 'date_end', 'students', 'subscription', 'edit', 'del'],
    'show' => 'table',
    'delDisplayField' => 'name',
];

if ($permissions['add']) {
    $_params['rel_actions'] = '<a class="ico-wt-sprite subs_add" href="index.php?r=' . $base_link_edition . '/add&amp;id_course=' . $model->getIdCourse() . '"><span>' . Lang::t('_ADD', 'subscribe') . '</span></a>';
}

$this->widget('table', $_params);

?>
</div>
<?php echo $back_link; ?>
<script type="text/javascript">
var StatusList = {
<?php
    $conds = [];
    $list = $model->getStatusForDropdown();
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