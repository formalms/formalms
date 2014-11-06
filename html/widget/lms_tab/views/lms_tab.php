<div id="middlearea" class="yui-navset">
    <?php
// Ale - non mi viene in mente modo più elegante :((
// la questione è di nascondere tutto il menu in quanto il catalog è stato tirato fuori...
   // if (!strpos($_GET['r'], 'atalog/')) {
        ?>
        <ul class="yui-nav">
            <?php
            $tab = array(
                'tb_elearning' => Lang::t('_ELEARNING', 'middlearea'),
                'tb_home' => Lang::t('_HOME', 'middlearea'),
                'tb_label' => Lang::t('_LABELS', 'label'),
                'tb_classroom' => Lang::t('_CLASSROOM', 'middlearea'),
                'tb_calendar' => Lang::t('_CALENDAR', 'middlearea'),
                'tb_catalog' => Lang::t('_CATALOGUE', 'middlearea'),
//                'tb_library' => Lang::t('_LIBRARY', 'middlearea'),
                'tb_assessment' => Lang::t('_ASSESSMENT', 'middlearea'),
                'tb_coursepath' => Lang::t('_COURSEPATH', 'coursepath'),
                'tb_games' => Lang::t('_CONTEST', 'middlearea'),
                'tb_communication' => Lang::t('_COMMUNICATIONS', 'middlearea'),
                'tb_videoconference' => Lang::t('_VIDEOCONFERENCE', 'middlearea'),
                'tb_kb' => Lang::t('_CONTENT_LIBRARY', 'middlearea')
            );
	
			if(Get::cfg('enable_plugins', false)){
				require_once(_adm_."/models/PluginAdm.php");
				$pluginAdm = new PluginAdm();

				$plugins=$pluginAdm->getInstalledPlugins();
				foreach ($plugins as $plugin_name){
					$tab["tb_".strtolower($plugin_name)]=ucfirst($plugin_name);
				}
			}

            $query_menu = "SELECT obj_index from %lms_middlearea where obj_index like 'tb_%' ORDER BY sequence";
            $re_tablist = sql_query($query_menu);

            while (list($obj_index) = sql_fetch_row($re_tablist)) {
                $id = $obj_index;
                $cid = substr($obj_index, 3);
                $name = $tab[$id];
                ?>

                <?php if ($this->isActive($cid)) : ?>
                    <li<?php echo $this->selected($cid); ?>>
                        <a href="index.php?r=lms/<?php echo $cid; ?>/show&sop=unregistercourse"><em><?php echo $name ?></em><?php //echo ( isset(${$name}) ? '<b>'.${$name}.'</b>' : '' );  ?></a></li>
                <?php endif; ?>


                <?php
            }
            ?>

        </ul>
        <?php
    //}
    ?>
    <div class="yui-content">
        <div id="tab_content" class="nested_tab">