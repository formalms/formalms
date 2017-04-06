<div id="middlearea" class="yui-navset">

    <?php
    $tab = array(
        'tb_elearning' => Lang::t('_ELEARNING', 'middlearea'),
        'tb_home' => Lang::t('_HOME', 'middlearea'),
        'tb_label' => Lang::t('_LABELS', 'label'),
        'tb_classroom' => Lang::t('_CLASSROOM', 'middlearea'),
        'tb_calendar' => Lang::t('_CALENDAR', 'middlearea'),
        'tb_catalog' => Lang::t('_CATALOGUE', 'middlearea'),
        'tb_assessment' => Lang::t('_ASSESSMENT', 'middlearea'),
        'tb_coursepath' => Lang::t('_COURSEPATH', 'coursepath'),
        'tb_games' => Lang::t('_CONTEST', 'middlearea'),
        'tb_communication' => Lang::t('_COMMUNICATIONS', 'middlearea'),
        'tb_videoconference' => Lang::t('_VIDEOCONFERENCE', 'middlearea'),
        'tb_kb' => Lang::t('_CONTENT_LIBRARY', 'middlearea')
    );

    $query_menu = "SELECT obj_index from %lms_middlearea where obj_index like 'tb_%' ORDER BY sequence";
    $re_tablist = sql_query($query_menu);

    $tablist_items = '';

    while (list($obj_index) = sql_fetch_row($re_tablist)) {
        $id = $obj_index;
        $cid = substr($obj_index, 3);
        $name = $tab[$id];
        if ($this->isActive($cid)) {
            $tablist_items .= '<li '.$this->selected($cid).'>';
            if (!strpos($_GET['r'], 'catalog')  && !isset($_GET['id_cat'])) {
                $tablist_items .= '<a href="index.php?r=lms/'.$cid.'/show&sop=unregistercourse">';
                    $tablist_items .= '<em>'.$name.'</em>';
                    // $tablist_items .= ( isset(${$name}) ? '<b>'.${$name}.'</b>' : '' );
                $tablist_items .= '</a>';
            }  
            $tablist_items .= '</li>';
        }
    }
    ?>

    <div class="tabs-wrapper">
        <ul class="nav nav-tabs hidden-xs">
            <?php echo $tablist_items; ?>
        </ul>

        <ul class="nav nav-tabs visible-xs">
            <script type="text/javascript">
                (function($) {
                    $(window).load(function() {
                        var dropdown_title = $('.tabs-dropdown').find('.active').text();
                        $('.dropdown-title').html(dropdown_title);
                    })
                })(jQuery);
            </script>
            <li class="dropdown">
              <a href="javascript:void(0)" class="dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="dropdown-title"></span> &nbsp;<span class="caret"></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-left tabs-dropdown">
                <?php echo $tablist_items; ?>
              </ul>
            </li>
        </ul>
    </div>
    <div class="yui-content">
        <div id="tab_content" class="nested_tab">   
        
        
        