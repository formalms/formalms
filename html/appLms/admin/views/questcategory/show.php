<?php
    echo getTitleArea(Lang::t('_TITLE_QCAT', 'questcategory'), 'questcategory');
?>
<div class="std_block">
<?php

$this->widget('tablefilter', [
    'id' => 'questcategory',
    'filter_text' => $filter_text,
    'js_callback_set' => 'QuestCategories.setFilter',
    'js_callback_reset' => 'QuestCategories.resetFilter',
]);

$_icon_mod = '<span class="ico-sprite subs_mod"><span>' . Lang::t('_MOD', 'standard') . '</span></span>';
$_icon_del = '<span class="ico-sprite subs_del"><span>' . Lang::t('_DEL', 'standard') . '</span></span>';

$_columns_arr = [
    ['key' => 'name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true],
    ['key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard'), 'sortable' => true],
];

if ($permissions['mod']) {
    $_columns_arr[] = ['key' => 'mod', 'label' => $_icon_mod, 'formatter' => 'doceboModify', 'className' => 'img-cell'];
}

if ($permissions['del']) {
    $_columns_arr[] = ['key' => 'del', 'label' => $_icon_del, 'formatter' => 'doceboDelete', 'className' => 'img-cell'];
}

$_table_params = [
    'id' => 'questcategory_table',
    'ajaxUrl' => 'ajax.adm_server.php?r=alms/questcategory/gettabledata',
    'rowsPerPage' => Forma\lib\Get::sett('visuItem', 25),
    'startIndex' => 0,
    'results' => Forma\lib\Get::sett('visuItem', 25),
    'sort' => 'name',
    'dir' => 'asc',
    'columns' => $_columns_arr,
    'fields' => ['id', 'name', 'description', 'used_test', 'used_poll', 'mod', 'del'],
    'generateRequest' => 'QuestCategories.requestBuilder',
    'delDisplayField' => 'name',
];

if ($permissions['add']) {
    $_table_params['rel_actions'] = [
        '<a id="add_qc_1" class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=alms/questcategory/create"><span>' . Lang::t('_ADD', 'standard') . '</span></a>',
        '<a id="add_qc_2" class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=alms/questcategory/create"><span>' . Lang::t('_ADD', 'standard') . '</span></a>',
    ];

    $this->widget('dialog', [
        'id' => 'add_questcategory_dialog',
        'dynamicContent' => true,
        'dynamicAjaxUrl' => true,
        'ajaxUrl' => 'this.href',
        'callback' => 'QuestCategories.createCallback',
        'callEvents' => [
            ['caller' => 'add_qc_1', 'event' => 'click'],
            ['caller' => 'add_qc_2', 'event' => 'click'],
        ],
    ]);
}

$this->widget('table', $_table_params);
?>
</div>
<script type="text/javascript">
var QuestCategories = {
	filterText: "",

	setFilter: function() {
		QuestCategories.filterText = $("input[id$='_filter_text']").val();
		DataTable_questcategory_table.refresh();
	},

	resetFilter: function() {
		this.value = "";
		QuestCategories.filterText = "";
		DataTable_questcategory_table.refresh();
	},

	createCallback: function(o) {
		this.destroy();
		DataTable_questcategory_table.refresh();
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
				"&filter_text=" + QuestCategories.filterText;
	},

	init: function(oConfig) {
		if (oConfig.filterText) this.filterText = oConfig.filterText;
	}
};

QuestCategories.init({
	filterText: "<?php echo $filter_text; ?>"
});
</script>