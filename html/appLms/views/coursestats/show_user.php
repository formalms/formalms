<style type="text/css">
  table.dataTable {
    overflow-x: scroll;
  }
  #coursestats td:nth-child(1), #coursestats th:nth-child(1) {
    display: none;
  }
</style>

<div class="std_block"><?php
  $columns = array(
    array('key' => 'id', 'label' => Lang::t('_ID.', 'standard'), 'sortable' => true, 'formatter' => 'CourseUserStats.id'),
    array('key' => 'path', 'label' => Lang::t('_ORD.', 'standard'), 'sortable' => true, 'formatter' => 'CourseUserStats.path'),
    array('key' => 'LO_name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true, 'formatter' => 'CourseUserStats.LOnameFormatter'),
    array('key' => 'LO_type', 'label' => Lang::t('_TYPE', 'standard'), 'sortable' => true),
    array('key' => 'LO_status', 'label' => Lang::t('_STATUS', 'standard'), 'sortable' => true, 'editor' => 'CourseUserStats.statusEditor'),
    array('key' => 'first_access', 'label' => Lang::t('_DATE_FIRST_ACCESS', 'standard'), 'sortable' => true, 'editor' => 'CourseUserStats.firstAccessEditor'),
    array('key' => 'last_access', 'label' => Lang::t('_DATE_LAST_ACCESS', 'standard'), 'sortable' => true, 'editor' => 'CourseUserStats.lastAccessEditor'),
    array('key' => 'history', 'label' => Lang::t('_ACCESS_DETAIL', 'standard'), 'sortable' => false),
    array('key' => 'totaltime', 'label' => Lang::t('_ACCESS_TOTAL_TIME', 'standard'), 'sortable' => true),
    array('key' => 'score', 'label' => Lang::t('_SCORE', 'standard'), 'sortable' => true),
  );

  $fields = array('id', 'LO_name', 'LO_type', 'LO_status', 'first_access', 'last_access', 'first_complete', 'last_complete',
    'first_access_timestamp', 'last_access_timestamp', 'first_complete_timestamp', 'last_complete_timestamp', 'history', 'totaltime', 'score');

  $rel_actions = '<a href="index.php?r=coursestats/export_csv2&id_user='.$id_user.'" class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_CSV', 'report').'">'
          .'<span>'.Lang::t('_EXPORT_CSV', 'report').'</span></a>';
  $rel_actions .= '<a href="index.php?r=coursestats/export_xls2&id_user='.$id_user.'" class="ico-wt-sprite subs_xls" title="'.Lang::t('_EXPORT_XLS', 'report').'">'
          .'<span>'.Lang::t('_EXPORT_XLS', 'report').'</span></a>';?>

  <table style="width:100%">
    <tr>
      <td colspan="1"><?php echo '<b>'.Lang::t('_USERNAME', 'standard').'</b>: '.$info->userid; ?></td>
      <td colspan="2"><?php echo '<b>'.Lang::t('_NAME', 'standard').'</b>: '.$info->firstname.' '.$info->lastname; ?></td>
    </tr>
    <tr>
      <td><?php echo '<b>'.Lang::t('_STATUS', 'course').'</b>: '.$info->course_status; ?></td>
      <td><?php echo '<b>'.Lang::t('_DATE_FIRST_ACCESS', 'course').'</b>: '.$info->first_access; ?></td>
      <td><?php echo '<b>'.Lang::t('_COMPLETED', 'course').'</b>: '.$info->date_complete; ?></td>
    </tr>
  </table>

  <br><?php
  echo $rel_actions;?>
  <br><br>

  <table class="table table-striped table-bordered display" style="width:100%" id="coursestats"></table>

  <br><?php
  echo $rel_actions;?>
  <br><br>
<?php
  echo getBackUi($base_url, Lang::t('_BACK', 'standard'));
?>
</div>

<script>

function editTableInit(tableId) {
  var table = $(tableId).FormaTable({
    rowId: 'id',
    processing: true,
    serverSide: true,
    columns: [
      <?php foreach($columns as $column) { ?>
        { data: "<?php echo $column['key'];?>", title: "<?php echo $column['label'];?>", orderable: "<?php echo $column['sortable'];?>" },
      <?php } ?>
    ],
    edit: {
      ajax: {
        url: 'ajax.server.php?r=coursestats/user_unique_inline_editor',
        type: "POST",
        data: {
          id_user: <?php echo (int)$id_user; ?>,
          id_course: <?php echo (int)$id_course; ?>,
        },
        complete: function(json) {},
      },
      columns: [ // per ogni colonna definisco i parametri necessari a fare l'edit
        {
          type: 'select',
          name: 'LO_status',
          fields: [
            { name: "<?php echo Lang::t('failed', 'standard');?>", value: 'failed'},
            { name: "<?php echo Lang::t('incomplete', 'standard');?>", value: 'incomplete'},
            { name: "<?php echo Lang::t('not_attempted', 'standard');?>", value: 'not attempted'},
            { name: "<?php echo Lang::t('attempted', 'standard');?>", value: 'attempted'},
            { name: "<?php echo Lang::t('ab-initio', 'standard');?>", value: 'ab-initio'},
            { name: "<?php echo Lang::t('completed', 'standard');?>", value: 'completed'},
            { name: "<?php echo Lang::t('passed', 'standard');?>", value: 'passed'},
          ],
        },
        {
          type: 'date',
          name: 'first_access',
          field: 'first_access',
        },
        {
          type: 'date',
          name: 'last_access',
          field: 'last_access',
        },
      ],
    },
    ajax: {
      url: 'ajax.server.php?r=coursestats/getusertabledata&id_user=<?php echo $id_user;?>',
      type: "GET",
      complete: function(json) {
        var content = '';
        var newContent = '';
        var id_lo = null;

        $.each($(tableId).find('tr'), function(i, item) {
          id_lo = $(item).attr('id');
          content = $(item).find('td:nth-child(3)').html();
          newContent = '<a href="index.php?r=coursestats/show_user_object&id_user='+ <?php echo (int)$id_user; ?> +'&id_lo=' + id_lo + '&from_user=1">' + content + '</a>';
          $(item).find('td:nth-child(3)').html(newContent);
        });
      },
    },
    order: [[ 1, "asc" ]],
  });
}

$(function() {
  editTableInit('#coursestats');

});
</script><?php

Util::get_js(Get::rel_path('lib') . '/lib.formatable.js', true, true);?>
