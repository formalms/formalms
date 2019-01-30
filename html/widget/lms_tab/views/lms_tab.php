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

    
 if(Get::cfg('enable_plugins', false)){
        $pl = new PluginManager();
        $list_pl = $pl->get_all_plugins();
        
        foreach ($list_pl as $key){
            $plugin_name = strtolower ($key['name']);
            $tab["tb_".$plugin_name] = Lang::t('_'.strtoupper ($key['name']), 'middlearea');
        }     
 }    
    
    
    
    
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
                $tablist_items .= '<a href="index.php?r=lms/mycourses/show&mycourses_tab=' . $obj_index . '&sop=unregistercourse">';
                    $tablist_items .= '<em>'.$name.'</em>';
                    // $tablist_items .= ( isset(${$name}) ? '<b>'.${$name}.'</b>' : '' );
                $tablist_items .= '</a>';
            }  
            $tablist_items .= '</li>';
        }
    }
    ?>

    <div class="tabs-wrapper">
        <ul class="nav nav-tabs hidden-xs slider-menu slider-menu--tabs">
            <?php echo $tablist_items; ?>
        </ul>
    </div>    
        
        
        