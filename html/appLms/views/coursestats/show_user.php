<script type="text/javascript">
	var CourseUserStats = {
		asyncSubmitter: function(callback, newData) {
			var new_value, old_value;
			var col = this.getColumn().key;
			var id_LO = this.getRecord().getData("id");
			//var datatable = this.getDataTable();

			switch (col) {
				case "first_access": {
					var date = this.calendar.getSelectedDates();
					old_value = this.getRecord().getData("first_access_timestamp");
					new_value = parseInt(date[0].getTime() / 1000); //we need time in seconds, not milliseconds
				} break;

				case "last_access": {
					var date = this.calendar.getSelectedDates();
					old_value = this.getRecord().getData("last_access_timestamp");
					new_value = parseInt(date[0].getTime() / 1000); //we need time in seconds, not milliseconds
				} break;

				case "first_complete": {
					var date = this.calendar.getSelectedDates();
					old_value = this.getRecord().getData("first_complete_timestamp");
					new_value = parseInt(date[0].getTime() / 1000); //we need time in seconds, not milliseconds
				} break;

				case "last_complete": {
					var date = this.calendar.getSelectedDates();
					old_value = this.getRecord().getData("last_complete_timestamp");
					new_value = parseInt(date[0].getTime() / 1000); //we need time in seconds, not milliseconds
				} break;

				default: {
					new_value = newData
					old_value = this.value;
				} break;
			}

			var ajaxCallback = {
				success: function(o) {
					var r = YAHOO.lang.JSON.parse(o.responseText);
					if (r.success) {
						callback(true, stripSlashes(r.new_value));
					} else {
						callback(/*true, stripSlashes(r.old_value)*/false);
					}
				},
				failure: {}
			}

			var postdata = "id_lo=" + id_LO
				+ "&id_user=" + <?php echo (int)$id_user; ?>
				+ "&id_course=" + <?php echo (int)$id_course; ?>
				+ "&col=" + col
				+ "&new_value=" + new_value
				+ "&old_value=" + old_value;

			var url = "ajax.server.php?r=coursestats/user_inline_editor";
			YAHOO.util.Connect.asyncRequest("POST", url, ajaxCallback, postdata);
		},

		statusEditor: null,
		firstAccessEditor: null,
		lastAccessEditor: null,
		firstCompleteEditor: null,
		lastCompleteEditor: null,

		LOnameFormatter: function(elLiner, oRecord, oColumn, oData) {
			var url = 'index.php?r=coursestats/show_user_object&amp;id_user='+<?php echo (int)$id_user; ?>+'&amp;id_lo='+oRecord.getData("id");
			elLiner.innerHTML = '<a href="'+url+'&from_user=1" title="">'+oData+'</a>';
		},

		tableInitEvent: function() {
			this.doBeforeShowCellEditor = function(oEditor) {
				var key = oEditor.getColumn().getKey();
				var dt = "";
				switch (key) {
					case "first_access":   
						var dt=oEditor.getRecord().getData("first_access_timestamp")
						if (dt==0){
							oEditor.value = new Date();
						}
						else{
							dt = dt*1000
							oEditor.value = new Date( dt );
						}					
						break;
					case "last_access":    
						var dt=oEditor.getRecord().getData("last_access_timestamp")
						if (dt==0){
							oEditor.value = new Date();
						}
						else{
							dt = dt*1000
							oEditor.value = new Date( dt );
						}	
						break;
					case "first_complete": 
						var dt=oEditor.getRecord().getData("first_complete_timestamp")
						if (dt==0){
							oEditor.value = new Date();
						}
						else{
							dt = dt*1000
							oEditor.value = new Date( dt );
						}	
						break;
					case "last_complete":  
						var dt=oEditor.getRecord().getData("last_complete_timestamp")
						if (dt==0){
							oEditor.value = new Date();
						}
						else{
							dt = dt*1000
							oEditor.value = new Date( dt );
						}	
						break;
				}
				return true;
			};
		},

		init: function() {
			this.statusEditor = new YAHOO.widget.DropdownCellEditor({
				asyncSubmitter: CourseUserStats.asyncSubmitter,
				dropdownOptions: <?php echo $status_list_js ?>
			});

			this.firstAccessEditor = new YAHOO.widget.DateCellEditor({
				asyncSubmitter: CourseUserStats.asyncSubmitter
			});

			this.lastAccessEditor = new YAHOO.widget.DateCellEditor({
				asyncSubmitter: CourseUserStats.asyncSubmitter
			});

			this.firstCompleteEditor = new YAHOO.widget.DateCellEditor({
				asyncSubmitter: CourseUserStats.asyncSubmitter
			});

			this.lastCompleteEditor = new YAHOO.widget.DateCellEditor({
				asyncSubmitter: CourseUserStats.asyncSubmitter
			});
		}
	}

	CourseUserStats.init();
</script>
<?php
$base_url = 'index.php?r=coursestats/show';
echo getTitleArea(array(
	$base_url => Lang::t('_COURSESTATS', 'menu_course'),
	$info->userid
));
?>
<div class="std_block">
<?php
echo getBackUi($base_url, Lang::t('_BACK', 'standard'));
?>
	<table style="width:100%">
		<tr>
			<td colspan="1"><?php echo '<b>'.Lang::t('_USERNAME', 'standard').'</b>: '.$info->userid; ?></td>
			<td colspan="2"><?php echo '<b>'.Lang::t('_NAME', 'standard').'</b>: '.$info->firstname.' '.$info->lastname; ?></td>
		</tr>
		<tr>
			<td><?php echo '<b>'.Lang::t('_STATUS', 'course').'</b>: '.$info->course_status; ?></td>
			<td><?php echo '<b>'.Lang::t('_DATE_FIRST_ACCESS', 'course').'</b>: '.$info->first_access; ?></td>
			<td><?php echo '<b>'.Lang::t('_COMPLETED', 'course').'</b>: '.$info->date_complete; ?></td>
			<!--<td><?php echo '<b>'.Lang::t('_DATE_LAST_ACCESS', 'course').'</b>: '.$info->last_access; ?></td>-->
		</tr>
	</table>
<?php

$columns = array(
	array('key' => 'LO_name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true, 'formatter' => 'CourseUserStats.LOnameFormatter'),
	array('key' => 'LO_type', 'label' => Lang::t('_TYPE', 'standard'), 'sortable' => true),
	array('key' => 'LO_status', 'label' => Lang::t('_STATUS', 'standard'), 'sortable' => true, 'editor' => 'CourseUserStats.statusEditor'),
	array('key' => 'first_access', 'label' => Lang::t('_DATE_FIRST_ACCESS', ''), 'sortable' => true, 'editor' => 'CourseUserStats.firstAccessEditor'),
	array('key' => 'last_access', 'label' => Lang::t('_DATE_LAST_ACCESS', ''), 'sortable' => true, 'editor' => 'CourseUserStats.lastAccessEditor'),
	array('key' => 'first_complete', 'label' => Lang::t('_DATE_FIRST_COMPLETE', ''), 'sortable' => true, 'editor' => 'CourseUserStats.firstCompleteEditor'),
	array('key' => 'last_complete', 'label' => Lang::t('_DATE_LAST_COMPLETE', ''), 'sortable' => true, 'editor' => 'CourseUserStats.lastCompleteEditor'),
	array('key' => 'score', 'label' => Lang::t('_SCORE', 'standard'), 'sortable' => true)
);

$fields = array('id', 'LO_name', 'LO_type', 'LO_status', 'first_access', 'last_access', 'first_complete', 'last_complete',
	'first_access_timestamp', 'last_access_timestamp', 'first_complete_timestamp', 'last_complete_timestamp', 'score');


$params = array(
	'id' => 'courseuserstats_table',
	'ajaxUrl' => 'ajax.server.php?r=coursestats/getusertabledata&id_user='.$id_user,
	'sort' => 'LO_name',
	'columns' => $columns,
	'fields' => $fields,
	'use_paginator' => false,
	'events' => array(
		'initEvent' => 'CourseUserStats.tableInitEvent'
	)
);

echo '<br />';
$this->widget('table', $params);
echo '<br />';

echo getBackUi($base_url, Lang::t('_BACK', 'standard'));
?>
</div>