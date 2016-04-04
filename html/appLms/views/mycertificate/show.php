<?php echo getTitleArea(Lang::t('_MY_CERTIFICATE', 'certificate')); ?>
<div class="std_block">
<?php
$icon_preview = '<span class="ico-sprite subs_view"><span>'.Lang::t('_PREVIEW', 'certificate').'</span></span>';
$icon_download = '<span class="ico-sprite subs_pdf"><span>'.Lang::t('_ALT_TAKE_A_COPY', 'certificate').'</span></span>';

// tabs
$selected_tab = Get::req('current_tab', DOTY_STRING, 'cert');
$tabs = '<div id="mycertificate_tabs" class="yui-navset">
        <ul class="yui-nav">
            <li' . ($selected_tab == 'cert' ? ' class="selected"' : '') . '><a href="#cert"><em>' . Lang::t('_CERTIFICATE', 'menu') . '</em></a></li>
            <li' . ($selected_tab == 'meta' ? ' class="selected"' : '') . '><a href="#meta"><em>' . Lang::t('_TITLE_META_CERTIFICATE', 'certificate') . '</em></a></li>
        </ul>
        <div class="yui-content">';
echo $tabs;

// certificate tab
echo '<div>';

$cert_columns = array(
    array('key' => 'year', 'label' => Lang::t('_YEAR', 'certificate'), 'className' => 'min-cell', 'sortable' => true),
    array('key' => 'code', 'label' => Lang::t('_COURSE_CODE', 'certificate')),
    array('key' => 'course_name', 'label' => Lang::t('_COURSE', 'certificate')),
    array('key' => 'cert_name', 'label' => Lang::t('_CERTIFICATE_NAME', 'course')),
    array('key' => 'date_complete', 'label' => Lang::t('_DATE_COMPLETE', 'certificate')),
    array('key' => 'preview', 'label' => $icon_preview, 'className' => 'img-cell'),
    array('key' => 'download', 'label' => $icon_download, 'className' => 'img-cell')
);

$this->widget('table', array(
    'id'                => 'cert_table',
    'ajaxUrl'		=> 'ajax.server.php?r=myCertificate/getMyCertificates',
    'columns'		=> $cert_columns,
    'rowsPerPage'	=> Get::sett('visuItem', 25),
    'startIndex'	=> 0,
    'results'		=> Get::sett('visuItem', 25),
    'sort'              => 'year',
    'dir'		=> 'desc',
    'fields'		=> array('year', 'code', 'course_name', 'cert_name', 'date_complete', 'preview', 'download'),
    'show'		=> 'table'
));

echo '</div>'; // close certificate tab 

// metacertificate tab
echo '<div>';

$meta_columns = array(
    array('key' => 'cert_code', 'label' => Lang::t('_CODE', 'certificate')),
    array('key' => 'cert_name', 'label' => Lang::t('_NAME')),
    array('key' => 'courses', 'label' => Lang::t('_COURSE_LIST')),
    array('key' => 'preview', 'label' => $icon_preview, 'className' => 'img-cell'),
    array('key' => 'download', 'label' => $icon_download, 'className' => 'img-cell')
);

$this->widget('table', array(
    'id'                => 'meta_table',
    'ajaxUrl'		=> 'ajax.server.php?r=myCertificate/getMyMetaCertificates',
    'columns'		=> $meta_columns,
    'rowsPerPage'	=> Get::sett('visuItem', 25),
    'startIndex'	=> 0,
    'results'		=> Get::sett('visuItem', 25),
    'fields'		=> array('cert_code', 'cert_name', 'courses', 'preview', 'download'),
    'show'		=> 'table'
));

echo '</div>'; // close metacertificate tab
echo '</div></div>'; // close tabs
echo '</div>'; //close std_block div

YuiLib::load('tabs');
cout('<script type="text/javascript">var myTabs = new YAHOO.widget.TabView("mycertificate_tabs");</script>', 'scripts');
?>