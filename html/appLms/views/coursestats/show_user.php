<?php

include Forma::inc(_lib_ . '/formatable/include.php');

  $fields = array('id', 'LO_name', 'LO_type', 'LO_status', 'first_access', 'last_access', 'first_complete', 'last_complete',
    'first_access_timestamp', 'last_access_timestamp', 'first_complete_timestamp', 'last_complete_timestamp', 'history', 'totaltime', 'score');

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

  <br><?
  echo $rel_actions;?>
  <br><br>

  <table class="table table-striped table-bordered display" style="width:100%" id="coursestats"></table>

  <br><?
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
            { 'data': 'id', 'title': '<?php echo Lang::t('_ID.', 'standard'); ?>', 'sortable': true },
            { 'data': 'path', 'title': '<?php echo Lang::t('_ORD.', 'standard'); ?>', 'sortable': true },
            { 'data': 'LO_name', 'title': '<?php echo Lang::t('_NAME', 'standard'); ?>', 'sortable': true },
            { 'data': 'LO_type', 'title': '<?php echo Lang::t('_TYPE', 'standard'); ?>', 'sortable': true },
            { 'data': 'LO_status', 'title': '<?php echo Lang::t('_STATUS', 'standard'); ?>', 'sortable': true,
                'edit': {
                    'type': 'select',
                    'options': {
                        'failed': 'Fallito',
                        'incomplete': 'Incompleto',
                        'not attempted': 'Non tentato',
                        'attempted': 'Tentato',
                        'ab-initio': 'Cominciato',
                        'completed': 'Completato',
                        'passed': 'passato'
                    }
                } },
            { 'data': 'first_access', 'title': '<?php echo Lang::t('_DATE_FIRST_ACCESS', ''); ?>', 'sortable': true, 'edit': { 'type': 'date' } },
            { 'data': 'last_access', 'title': '<?php echo Lang::t('_DATE_LAST_ACCESS', ''); ?>', 'sortable': true, 'edit': { 'type': 'date' } },
            { 'data': 'history', 'title': 'Accessi in dettaglio ', 'sortable': false },
            { 'data': 'totaltime', 'title': 'Tempo totale accessi', 'sortable': true },
            { 'data': 'score', 'title': '<?php echo Lang::t('_SCORE', 'standard'); ?>', 'sortable': true } 
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