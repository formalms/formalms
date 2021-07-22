<?php

include Forma::inc(_lib_ . '/formatable/include.php');

  $rel_actions = '<a href="index.php?r=coursestats/export_csv2&id_user='.$id_user.'" class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_CSV', 'report').'">'
          .'<span>'.Lang::t('_EXPORT_CSV', 'report').'</span></a>';
  $rel_actions .= '<a href="index.php?r=coursestats/export_xls2&id_user='.$id_user.'" class="ico-wt-sprite subs_xls" title="'.Lang::t('_EXPORT_XLS', 'report').'">'
          .'<span>'.Lang::t('_EXPORT_XLS', 'report').'</span></a>';
?>
<style type="text/css">
  table.dataTable {
    overflow-x: scroll;
  }
  #coursestats td:nth-child(1), #coursestats th:nth-child(1) {
    display: none;
  }
</style>
<div class="std_block">
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
$(function() {
    var table = $('#coursestats').FormaTable({
        rowId: "id",
        processing: true,
        serverSide: true,
        columns: [
            { data: 'id', title: '<?php echo Lang::t('_ID.', 'standard'); ?>', sortable: true },
            { data: 'path', title: '<?php echo Lang::t('_ORD.', 'standard'); ?>', sortable: true },
            { data: 'LO_name', title: '<?php echo Lang::t('_NAME', 'standard'); ?>', sortable: true, 
                render: function(data, type, row, meta) {
                    if(type === 'display') {
                        return '<a href="index.php?r=coursestats/show_user_object&id_user='+ <?php echo $id_user; ?> +'&id_lo=' + row.id + '&from_user=1">' + data + '</a>';
                    } else {
                        return data;
                    }
                } },
            { data: 'LO_type', title: '<?php echo Lang::t('_TYPE', 'standard'); ?>', sortable: true },
            { data: 'LO_status', title: '<?php echo Lang::t('_STATUS', 'standard'); ?>', sortable: true,
                edit: {
                    type: 'select',
                    options: {
                        'failed': '<?php echo Lang::t('failed', 'standard');?>',
                        'incomplete': '<?php echo Lang::t('incomplete', 'standard');?>',
                        'not attempted': '<?php echo Lang::t('not_attempted', 'standard');?>',
                        'attempted': '<?php echo Lang::t('attempted', 'standard');?>',
                        'ab-initio': '<?php echo Lang::t('ab-initio', 'standard');?>',
                        'completed': '<?php echo Lang::t('completed', 'standard');?>',
                        'passed': '<?php echo Lang::t('passed', 'standard');?>'
                    }
                } },
            { data: 'first_access', title: '<?php echo Lang::t('_DATE_FIRST_ACCESS', ''); ?>', sortable: true, edit: { type: 'date' } },
            { data: 'last_access', title: '<?php echo Lang::t('_DATE_LAST_ACCESS', ''); ?>', sortable: true, edit: { type: 'date' } },
            { data: 'history', title: '<?php echo Lang::t('_ACCESS_DETAIL', 'standard'); ?>', sortable: false },
            { data: 'totaltime', title: '<?php echo Lang::t('_ACCESS_TOTAL_TIME', 'standard'); ?>', sortable: true },
            { data: 'score', title: '<?php echo Lang::t('_SCORE', 'standard'); ?>', sortable: true } 
        ],
        ajax: {
            url: "ajax.server.php?r=coursestats/getusertabledata&id_user=<?php echo $id_user;?>",
            type: "GET"
        },
        order: [[ 1, "asc" ]],
        edit: {
            url: "ajax.server.php?r=coursestats/user_inline_editor",
            type: "POST",
            data: {
                id_user: <?php echo $id_user;?>,
                id_course: <?php echo $id_course;?>
            },
            id: 'id_lo'
        }
    });
});
</script>