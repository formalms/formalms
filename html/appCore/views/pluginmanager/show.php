<?php echo getTitleArea(Lang::t('_PLUGIN_LIST', 'configuration')); ?>

<div class="std_block">

    <div id="global_conf" class="tabs-wrapper">

        <ul class="nav nav-tabs bordered">
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" role="button" data-toggle="dropdown">
                    <span class="dropdown-title"><?php echo Lang::t('_OTHER_OPTION', 'course'); ?></span> &nbsp;<span
                        class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-left tabs-dropdown">
                    <?php
                    while (list($id, $canonical_name) = each($plugins)) {

                        echo '<li' . ((($plugins_info[$canonical_name]['id'] == $active_tab) || (!$active_tab && $id == 0)) ? ' class="active"' : '') . '>'
                            . '<a data-toggle="tab" href="#tab_plugin_' . $id . '"><em>' . Lang::t('_' . strtoupper($canonical_name), 'configuration') . '</em></a>'
                            . '</li>';
                    }
                    reset($plugins);
                    ?>
                </ul>
            </li>
        </ul>
        <div class="tab-content">
            <?php
            $model->plugins = $plugins;
            while (list($id, $canonical_name) = each($plugins)) {

                echo '<div id="tab_plugin_' . $id . '" class="tab-pane' . ((($plugins_info[$canonical_name]['id'] == $active_tab) || (!$active_tab && $id == 0)) ? ' active' : '') . '">'
                    //.'<h2>'.Lang::t('_'.$plugins_info[strtoupper($canonical_name)][''], 'configuration').'</h2>'
                    //.'<p>'.Lang::t('_CONF_DESCR_'.$id, 'configuration').'</p>'

                    . Form::openForm('conf_option_' . $id, 'index.php?r=adm/pluginmanager/save');


                $model->printPageWithElement($id, $canonical_name);

                echo Form::closeElementSpace()
                    . Form::openButtonSpace()
                    . Form::getButton('save_config_' . $id, 'save_config', Lang::t('_SAVE', 'configuration'))
                    . Form::getButton('undo_' . $id, 'undo', Lang::t('_UNDO', 'configuration'))
                    . Form::closeButtonSpace()
                    . Form::CloseForm()
                    . '<br />'
                    . '</div>';
            }

            ?>
        </div>
        <div class="nofloat">&nbsp;</div>
    </div>
</div>
<script type="text/javascript">
    /*YAHOO.util.Event.onDOMReady(function () {
        var targets = YAHOO.util.Selector.query("span[id^=tt_target]");
        new YAHOO.widget.Tooltip("tooltip_info", {
            context: targets,
            effect: {effect: YAHOO.widget.ContainerEffect.FADE, duration: 0.20}
        });
        new YAHOO.widget.TabView('global_conf', {orientation: 'left'});
    });*/
</script>