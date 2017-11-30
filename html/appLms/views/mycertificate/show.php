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
);

$this->widget('table', array(
    'id'                => 'cert_table',
    'ajaxUrl'		=> 'ajax.server.php?r=mycertificate/getMyCertificates',
    'columns'		=> $cert_columns,
    'rowsPerPage'	=> Get::sett('visuItem', 25),
    'startIndex'	=> 0,
    'results'		=> Get::sett('visuItem', 25),
    'sort'              => 'year',
    'dir'		=> 'desc',
    'fields'		=> array('year', 'code', 'course_name', 'cert_name', 'date_complete', 'download'),
    'show'		=> 'table'
));

echo '</div>'; // close certificate tab 

// metacertificate tab
echo '<div role="tabpanel" class="tab-pane fade" id="meta">';

$meta_columns = array(
    array('key' => 'cert_code', 'label' => Lang::t('_CODE', 'certificate')),
    array('key' => 'cert_name', 'label' => Lang::t('_NAME')),
    array('key' => 'courses', 'label' => Lang::t('_COURSE_LIST')),
    array('key' => 'download', 'label' => $icon_download, 'className' => 'img-cell')
);

$this->widget('table', array(
    'id'                => 'meta_table',
    'ajaxUrl'		=> 'ajax.server.php?r=mycertificate/getMyMetaCertificates',
    'columns'		=> $meta_columns,
    'rowsPerPage'	=> Get::sett('visuItem', 25),
    'startIndex'	=> 0,
    'results'		=> Get::sett('visuItem', 25),
    'fields'		=> array('cert_code', 'cert_name', 'courses', 'preview', 'download'),
    'show'		=> 'table'
));

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