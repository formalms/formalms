<?php
echo getTitleArea([Lang::t('_CONTEST', 'games')]);
?>
<div class="std_block">
<?php

$_columns = [
    ['key' => 'title', 'label' => Lang::t('_TITLE', 'games'), 'sortable' => true],
    ['key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'games'), 'sortable' => true],
    ['key' => 'start_date', 'label' => Lang::t('_START_DATE', 'games'), 'sortable' => true, 'className' => 'image'],
    ['key' => 'end_date', 'label' => Lang::t('_DATE_END', 'games'), 'sortable' => true, 'className' => 'image'],
    ['key' => 'type_of', 'label' => Lang::t('_TYPE', 'games'), 'sortable' => true, 'className' => 'image'],
];

if ($permissions['mod']) {
    $_columns[] = ['key' => 'categorize', 'label' => '<span class="ico-sprite subs_categorize"><span>' . Lang::t('_CATEGORIZE', 'kb') . '</span></span>', 'className' => 'img-cell'];
}

if ($permissions['subscribe']) {
    $_columns[] = ['key' => 'user', 'label' => '<span class="ico-sprite subs_user"><span>' . Lang::t('_ASSIGN_USERS', 'games') . '</span></span>', 'className' => 'img-cell'];
}

if ($permissions['mod']) {
    $_columns[] = ['key' => 'edit', 'label' => '<span class="ico-sprite subs_mod"><span>' . Lang::t('_MOD', 'games') . '</span></span>', 'className' => 'img-cell'];
}

if ($permissions['del']) {
    $_columns[] = ['key' => 'del', 'label' => '<span class="ico-sprite subs_del"><span>' . Lang::t('_DEL', 'games') . '</span></span>', 'formatter' => 'doceboDelete', 'className' => 'img-cell'];
}

$_params = [
    'id' => 'edition_table',
    'ajaxUrl' => 'ajax.adm_server.php?r=alms/games/getlist',
    'rowsPerPage' => FormaLms\lib\Get::sett('visuItem', 25),
    'startIndex' => 0,
    'results' => FormaLms\lib\Get::sett('visuItem', 25),
    'sort' => 'title',
    'dir' => 'asc',
    'columns' => $_columns,
    'fields' => ['id', 'id_game', 'title', 'description', 'start_date', 'end_date', 'type_of', 'categorize', 'id_resource', 'user', 'edit', 'del'],
    'delDisplayField' => 'title',
];

if ($permissions['add']) {
    $_params['rel_actions'] = '<a class="ico-wt-sprite subs_add" href="index.php?r=alms/games/add"><span>' . Lang::t('_ADD', 'games') . '</span></a>';
}

$this->widget('table', $_params);

?>
</div>