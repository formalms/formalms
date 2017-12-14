<?php
require_once(_base_.'/lib/lib.table.php');

echo getTitleArea(Lang::t('_PLUGIN_LIST', 'configuration'));

$table = new Table(0, Lang::t('_PLUGIN_LIST', 'configuration'), Lang::t('_PLUGIN_LIST', 'configuration'));

$table->setTableId("table_plugin");

$cont_h = array(
    Lang::t('_PLUGIN_NAME', 'configuration'),
    Lang::t('_PLUGIN_VERSION', 'configuration'),
    Lang::t('_PLUGIN_AUTHOR', 'configuration'),
    Lang::t('_PLUGIN_CATEGORY', 'configuration'),
    Lang::t('_PLUGIN_DESCRIPTION', 'configuration'),
    "",
    "",
    ""
);

$type_h = array('', 'align_center', 'align_center', '', '');

$table->setColsStyle($type_h);

$table->addHead($cont_h);

foreach ($plugins as $info){
    $errors="";
    $settings="";
    $install="";
    $activate="";
    //if already in database
    if (isset($info['plugin_id'])){
        if ($info['core']==="0"){
            if (!$info['version_error']){
                if ($info['dependence_of']){
                    $dependencies = "";
                    foreach ($info['dependence_of'] as $k => $v){
                        $dependencies .= "\n".$k.": ".$v;
                    }
                    $install.='<div style="color: grey; cursor: help;" title="'.Lang::t('_PLUGIN_ERROR_UNINSTALL_DEPENDENCE', 'configuration').' '.$dependencies.'">'.Lang::t('_PLUGIN_UNINSTALL', 'configuration').'</div>';
                    $activate = '<div style="color: grey;cursor: help;" title="'.Lang::t('_PLUGIN_ERROR_DEACTIVATE_DEPENDENCE', 'configuration').' '.$dependencies.'">'.Lang::t('_PLUGIN_DEACTIVATE', 'configuration').'</div>';
                } else if ($info['update']){
                    $error="";
                    if (!class_exists('ZipArchive')){
                        $error.= Lang::t('_PLUGIN_ERROR_NOT_ONLINE_UPDATE', 'configuration')."<br>";
                    }
                    $install.= '<a title="'.$error.'" style="color: #006d07;" href="index.php?r=adm/pluginmanager/update'.'&plugin='.$info['name'].'&online='.$info['online'].'">'.Lang::t('_PLUGIN_UPDATE', 'configuration').'</a>';
                } else {
                    $install.='<a style="color: #C84000;" href="javascript:askUninstall(\'index.php?r=adm/pluginmanager/uninstall'.'&plugin='.$info['name'].'\');">'.Lang::t('_PLUGIN_UNINSTALL', 'configuration').'</a>';
                    //if active
                    if ($info['active']=="1"){
                        $activate.=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/deactivate'.'&plugin='.$info['name'].'">'.Lang::t('_PLUGIN_DEACTIVATE', 'configuration').'</a>';
                        //if not active
                    } else {
                        $activate.=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/activate'.'&plugin='.$info['name'].'">'.Lang::t('_PLUGIN_ACTIVATE', 'configuration').'</a>';
                    }
                }
            } else {
                $errors.= '<a style="color: #c80014;" href="javascript:;">'.Lang::t('_PLUGIN_ERROR_OLD_VERSION', 'configuration').'</a>';
            }
        } else {
            $install.='<div style="color: grey;cursor: help;"title="'.Lang::t('_PLUGIN_ERROR_CORE_UNINSTALL', 'configuration').'">'.Lang::t('_PLUGIN_UNINSTALL', 'configuration').'</div>';
            $activate = '<div style="color: grey;cursor: help;" title="'.Lang::t('_PLUGIN_ERROR_CORE_DEACTIVATE', 'configuration').'">'.Lang::t('_PLUGIN_DEACTIVATE', 'configuration').'</div>';
        }
        $settings=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/showSettings'.'&plugin='.$info['name'].'">'.Lang::t('_PLUGIN_SETTINGS', 'configuration').'</a>';
        //if not in database
    } else {
        if (!$info['dependencies_unsatisfied']){
            $install.='<a style="color: #C84000;" href="index.php?r=adm/pluginmanager/install&plugin='.$info['name'].'">'.Lang::t('_PLUGIN_INSTALL', 'configuration').'</a>';
            $install.=' <a style="color: red;" href="javascript:askPurge(\'index.php?r=adm/pluginmanager/purge'.'&plugin='.$info['name'].'\');">'.Lang::t('_PLUGIN_PURGE', 'configuration').'</a>';
        } else {
            $dependencies = "";
            foreach ($info['dependencies_unsatisfied'] as $k => $v){
                $dependencies .= "\n".$k.": ".$v;
            }
            $install.='<div style="color: grey;cursor: help;" title="'.Lang::t('_PLUGIN_ERROR_NO_DEPENDENCIES', 'configuration').':'.$dependencies.'">'.Lang::t('_PLUGIN_INSTALL', 'configuration').'</div>';
        }
    }
    $table->addBody(array(
        $info['title'],
        $info['version'],
        $info['author'],
        $info['category'],
        $info['description'],
        $settings,
        $install,
        $activate
    ));
}
echo "<h2>".Lang::t('_PLUGIN_UPLOAD', 'configuration')."</h2><br/><br/>";
echo Form::openForm( "plugin_upload" , "index.php?r=adm/pluginmanager/upload", false, false, "multipart/form-data");
echo Form::getInputFilefield("", "plugin_file_upload", "plugin_file_upload");
echo "<br/>";
echo Form::getButton("", "submit_upload", "Upload", "btn btn-primary");
echo Form::closeForm();
echo "<hr/>";
echo $table->getTable();

echo '
<script>
function askUninstall(link){
    if (confirm("'.Lang::t('_PLUGIN_UNINSTALL_CONFIRMATION', 'configuration').'")) {
        location.href=link;
    }
}
function askPurge(link){
    if (confirm("'.Lang::t('_PLUGIN_PURGE_CONFIRMATION', 'configuration').'")) {
        location.href=link;
    }
}
</script>
';