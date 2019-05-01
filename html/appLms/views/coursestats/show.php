<?php

include Forma::inc(_lib_ . '/formatable/include.php');
Util::get_js(Get::rel_path('lms') . '/views/coursestats/coursestats.js', true, true);

echo getTitleArea(Lang::t('_COURSESTATS', 'menu_course'));
?>
<div class="std_block"><?php

$columns = array(
	array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'sortable' => true, 'formatter' => 'CourseStats.useridFormatter', 'className' => 'min-cell'),
	array('key' => 'fullname', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true, 'formatter' => 'CourseStats.fullnameFormatter', 'className' => 'min-cell'),
	array('key' => 'level', 'label' => Lang::t('_LEVEL', 'standard'), 'sortable' => true, 'className' => 'min-cell'),
	array('key' => 'status', 'label' => Lang::t('_STATUS', 'standard'), 'sortable' => true, 'className' => 'min-cell', 'editor' => 'CourseStats.statusEditor')
);

foreach ($lo_list as $lo) {
	$icon = '('.$lo->type.')';
	$link = '';
	switch ($lo->type) {
		case "poll": $link = '<a title="" href="index.php?r=coursestats/show_object&id_lo='.(int)$lo->id.'">'.$lo->title.'</a>'; break;
		default: $link = $lo->title;
	}
	$columns[] = array('key' => 'lo_'.$lo->id, 'label' => $link.'<br />'.$icon, 'sortable' => false, 'formatter' => 'CourseStats.LOFormatter', 'className' => 'min-cell');
}

$columns[] = array('key' => 'completed', 'label' => Lang::t('_COMPLETED', 'course'), 'sortable' => false, 'formatter' => 'CourseStats.completedFormatter', 'className' => 'min-cell');

$fields = array('id', 'userid', 'firstname', 'lastname', 'level', 'status', 'status_id', 'completed');
foreach ($lo_list as $lo) {
	$fields[] = 'lo_'.$lo->id;
}
$a=$a;
$rel_actions = '
	<a href="index.php?r=coursestats/export_csv" class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_CSV', 'report').'">
		<span>'.Lang::t('_EXPORT_CSV', 'report').'</span>
	</a>
	<a href="index.php?r=coursestats/export_csv3" class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_REPORT_DETAIL_CSV', 'report').'">
		<span>'.Lang::t('_EXPORT_REPORT_DETAIL_CSV', 'report').'</span>
	</a>
	<a href="index.php?r=coursestats/export_Xls" class="ico-wt-sprite subs_xls" title="'.Lang::t('_EXPORT_REPORT_DETAIL_XLS', 'report').'">
		<span>'.Lang::t('_EXPORT_REPORT_DETAIL_XLS', 'report').'</span>
	</a>';?>

  <br><?php
  echo $rel_actions;?>
  <br><br>

<table class="table table-striped table-bordered display" style="width:100%" id="coursestats">
  <thead>
    <tr><?php
      foreach ($columns as $column) {?>
        <th scope="col"><b><?php echo $column['label'];?></b></th><?php
      }?>
    </tr>
  </thead>
</table>

  <br><?php
  echo $rel_actions;?>
  <br><br>
</div>

<script>
$(function() {
	var tableId = '#coursestats';

	var tableFields =	[
		{ name: 'status', field: 'LO_status', date: false, position: 5 },
    { name: 'first_access', field: 'first_access', date: true, position: 6 },
    { name: 'last_access', field: 'last_access', date: true, position: 7 },
  ];

  $(tableId).FormaTable({
    processing: true,
    serverSide: true,
    /*columns: [
      <?php foreach($columns as $column) { ?>
        { data: "<?php echo $column['key'];?>", title: "<?php echo $column['label'];?>", orderable: "<?php echo $column['sortable'];?>" },
      <?php } ?>
    ],*/
    scrollX: true,
    order: [[ 0, "asc" ]],
    ajax: {
      url: 'ajax.server.php?r=coursestats/gettabledata',
      type: "POST",
      complete: function(json) {
        var tr = $(tableId).find('> tbody > tr');

        $.each(tableFields, function(i, item) {
          $(tr).find('> td:nth-child(' + item.position + ')').attr('data-field', item.name);
        });
      },
    },
  });
});
</script>