<?php FormaLms\lib\Get::title([
    'index.php?r=alms/location/show' => Lang::t('_LOCATION', 'classroom'),
    'index.php?r=alms/location/show_classroom&amp;id_location=' . $info->location_id => Lang::t('_CLASSROOM', 'classroom'),
    Lang::t('_CALENDAR', 'classroom'),
]); ?>
<div class="std_block">
    <b><?php echo $classroomName; ?></b>    
	<?php echo getBackUi('index.php?r=alms/location/show_classroom&amp;id_location=' . $info->location_id, Lang::t('_BACK')); ?>
	<div id="classroom_calendar"></div>
	<div class="nofloat"></div>
	<script type="text/javascript">
	var datecount = 0;
	YAHOO.util.Event.onDOMReady(function() {

		cal1 = new YAHOO.widget.CalendarGroup("cal1","classroom_calendar", {PAGES:3});

		cal1.cfg.setProperty("DATE_FIELD", "/");
		cal1.cfg.setProperty("DATE_RANGE_DELIMITER", ".");
		cal1.cfg.setProperty("DATE_FIELD_DELIMITER", "-");
		cal1.cfg.setProperty("MDY_YEAR_POSITION", 1);
		cal1.cfg.setProperty("MDY_MONTH_POSITION", 2);
		cal1.cfg.setProperty("MDY_DAY_POSITION", 3);

		<?php if ($date_list) {
    foreach ($date_list as $value) { ?>
			
			cal1.addRenderer("<?php echo $value; ?>", cal1.renderCellStyleHighlight1);

		<?php }
} ?>
		cal1.render();

		cal1.changePageEvent.subscribe(function(type, args) {
			var fromDate = args[0];
			var toDate = args[1];
			var currentDate = cal1.cfg.getProperty("pagedate");
	
			if(datecount == 2) {
				Calendar.dateRange = args[0].getMonth()+"-"+args[0].getFullYear();
				DataTable_classroom_date_list.refresh();
				datecount = 0;
			}
			else datecount ++;
		});

	});

	</script>
<?php

if ($date_list) {
    $this->widget('table', [
        'id' => 'classroom_date_list',
        'ajaxUrl' => 'ajax.adm_server.php?r=alms/location/getclassroomdates&id_classroom=' . (int) $id_classroom,
        'sort' => 'date',
        'columns' => [
            ['key' => 'date',
                'label' => Lang::t('_DATE', 'lms'),
                'sortable' => true, ],
                    ['key' => 'hour_start',
                        'label' => Lang::t('_HOUR_END', 'course'),
                        'sortable' => true, ],
                    ['key' => 'pause_begin',
                        'label' => Lang::t('_PAUSE_BEGIN', 'course'),
                        'sortable' => true, ],
                    ['key' => 'pause_end',
                        'label' => Lang::t('_PAUSE_END', 'course'),
                        'sortable' => true, ],
                    ['key' => 'hour_end',
                        'label' => Lang::t('_HOUR_END', 'course'),
                        'sortable' => true, ],
                ['key' => 'name',
                    'label' => Lang::t('_COURSE', 'lms'),
                'sortable' => true, ],
        ],
        'generateRequest' => 'Calendar.RequestBuilder',
        'fields' => ['date', 'hour_start', 'pause_begin', 'pause_end', 'hour_end', 'name'],
        'delDisplayField' => 'date',
    ]);
}

?>
	<script type="text/javascript">

	var Calendar = {

		dateRange: false,

		modFormatter: function(elLiner, oRecord, oColumn, oData) {
			var id = oRecord.getData("id_classroom");
			elLiner.innerHTML = '<a href="index.php?r=alms/location/updateClassroom&amp;id_classroom='+id+'" '
				+'class="ico-sprite subs_mod" title="<?php echo Lang::t('_MOD', 'standard'); ?>">'
				+'<span><?php echo Lang::t('_MOD', 'standard'); ?></span></a>';
		},


		SchedFormatter: function(elLiner, oRecord, oColumn, oData) {
			var id = oRecord.getData("id_classroom");
			elLiner.innerHTML = '<a href="index.php?r=alms/location/show_calendar&amp;id_classroom='+id+'" '
				+'class="ico-sprite subs_mod" title="<?php echo Lang::t('_SCHEDULE', 'standard'); ?>">'
				+'<span><?php echo Lang::t('_SCHEDULE', 'standard'); ?></span></a>';
		},


		RequestBuilder: function (oState, oSelf) {
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
					"&date_range=" + Calendar.dateRange;
		}
	}

	</script>
	<?php echo getBackUi('index.php?r=alms/location/show_classroom&amp;id_location=' . $info->location_id, Lang::t('_BACK')); ?>
</div>

