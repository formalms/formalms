<?php echo getTitleArea(Lang::t('_MY_CERTIFICATE', 'certificate')); ?>
<div>
<?php
$icon_preview = '<span class="ico-sprite subs_view"><span>'.Lang::t('_PREVIEW', 'certificate').'</span></span>';
$icon_download = '<span class="ico-sprite subs_pdf"><span>'.Lang::t('_ALT_TAKE_A_COPY', 'certificate').'</span></span>';

// tabs
$selected_tab = Get::req('current_tab', DOTY_STRING, 'cert');
$tabs = '<ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active" ' . ($selected_tab == 'cert' ? ' class="selected"' : '') . '><a href="#cert" aria-controls="cert" role="tab" data-toggle="tab"><em>' . Lang::t('_CERTIFICATE', 'menu') . '</em></a></li>';

if ($totalMetaCertificates){
    $tabs .= '<li role="presentation" ' . ($selected_tab == 'meta' ? ' class="selected"' : '') . '><a href="#meta" aria-controls="meta" role="tab" data-toggle="tab"><em>' . Lang::t('_TITLE_META_CERTIFICATE', 'certificate') . '</em></a></li>';
}
$tabs .= '</ul>
        <div class="tab-content">';

echo $tabs;

// certificate tab
echo '<div role="tabpanel" class="tab-pane fade in active" id="cert">';

$cert_columns = array(
    array('key' => 'year', 'label' => Lang::t('_YEAR', 'certificate'), 'className' => 'min-cell', 'sortable' => true),
    array('key' => 'code', 'label' => Lang::t('_COURSE_CODE', 'certificate')),
    array('key' => 'course_name', 'label' => Lang::t('_COURSE', 'certificate')),
    array('key' => 'cert_name', 'label' => Lang::t('_CERTIFICATE_NAME', 'course')),
    array('key' => 'date_complete', 'label' => Lang::t('_DATE_COMPLETE', 'certificate')),
    array('key' => 'download', 'label' => $icon_download, 'className' => 'img-cell')
);?>

<table class="table table-striped table-bordered display" style="width:100%" id="mycertificates">
  <thead>
    <tr><?php
      foreach ($cert_columns as $column) {?>
        <th scope="col"><b><?php echo $column['label'];?></b></th><?php
      }?>
    </tr>
  </thead>
</table><?php

echo '</div>'; // close certificate tab 

// metacertificate tab
echo '<div role="tabpanel" class="tab-pane fade" id="meta">';

$meta_columns = array(
    array('key' => 'cert_code', 'label' => Lang::t('_CODE', 'certificate')),
    array('key' => 'cert_name', 'label' => Lang::t('_NAME')),
    array('key' => 'courses', 'label' => Lang::t('_COURSE_LIST')),
    array('key' => 'download', 'label' => $icon_download, 'className' => 'img-cell')
);
/*
$this->widget('table', array(
    'id'                => 'meta_table',
    'ajaxUrl'		=> 'ajax.server.php?r=mycertificate/getMyMetaCertificates',
    'columns'		=> $meta_columns,
    'rowsPerPage'	=> Get::sett('visuItem', 25),
    'startIndex'	=> 0,
    'results'		=> Get::sett('visuItem', 25),
    'fields'		=> array('cert_code', 'cert_name', 'courses', 'preview', 'download'),
    'show'		=> 'table'
));*/

echo '</div>'; // close metacertificate tab
echo '</div>'; // close tabs
echo '</div>'; //close std_block div

cout('<script type="text/javascript">
$("body").on("click", ".subs_pdf", function () {
        $(this).attr("title", "'.Lang::t('_DOWNLOAD', 'certificate').'");
        $(this).children("span").text("'.Lang::t('_DOWNLOAD', 'certificate').'");
    });
</script>', 'scripts');
?>



<script>
$(function() {
    var tableId = '#mycertificates';

    /*var tableFields =   [
        { name: 'status', field: 'LO_status', date: false, position: 5 },
    { name: 'first_access', field: 'first_access', date: true, position: 6 },
    { name: 'last_access', field: 'last_access', date: true, position: 7 },
  ];*/

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
      url: 'ajax.server.php?r=mycertificate/getMyCertificates',
      type: "POST",
      complete: function(json) {
        var tr = $(tableId).find('> tbody > tr');

        /*$.each(tableFields, function(i, item) {
          $(tr).find('> td:nth-child(' + item.position + ')').attr('data-field', item.name);
        });*/
      },
    },
  });
});
</script><?php

Util::get_js(Get::rel_path('lib') . '/lib.formatable.js', true, true);