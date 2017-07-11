<?php
require_once(_base_.'/lib/lib.table.php');

echo getTitleArea(Lang::t('_PLUGIN_LIST', 'configuration'));

$table = new Table(0, Lang::t('_PLUGIN_LIST', 'configuration'), Lang::t('_PLUGIN_LIST', 'configuration'));

$table->setTableId("table_plugin");

$cont_h = array(
    "Nome",
    "Versione",
    "Autore",
    "Categoria",
    "Descrizione",
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
                    $install.='<div style="color: grey; cursor: help;" title="Cannot uninstall because is a dependence of '.$dependencies.'">Disinstalla</div>';
                    $activate = '<div style="color: grey;cursor: help;" title="Cannot deactivate because is a dependence of '.$dependencies.'">Disattiva</div>';
                } else if ($info['update']){
                    $error="";
                    if (!class_exists('ZipArchive')){
                        $error.= "You can't use the online feautures<br>";
                    }
                    $install.= '<a title="'.$error.'" style="color: #006d07;" href="index.php?r=adm/pluginmanager/update'.'&plugin='.$info['name'].'&online='.$info['online'].'">Update</a>';
                } else {
                    $install.='<a style="color: #C84000;" href="javascript:askUninstall(\'index.php?r=adm/pluginmanager/uninstall'.'&plugin='.$info['name'].'\');">Disinstalla</a>';
                    //if active
                    if ($info['active']=="1"){
                        $activate.=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/deactivate'.'&plugin='.$info['name'].'">Disattiva</a>';
                        //if not active
                    } else {
                        $activate.=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/activate'.'&plugin='.$info['name'].'">Attiva</a>';
                    }
                }
            } else {
                $errors.= '<a style="color: #c80014;" href="javascript:;">Versione più vecchia</a>';
            }
        } else {
            $install.='<div style="color: grey;cursor: help;"title="Cannot uninstall because is core">Disinstalla</div>';
            $activate = '<div style="color: grey;cursor: help;" title="Cannot deactivate because is core">Disattiva</div>';
        }
        $settings=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/showSettings'.'&plugin='.$info['name'].'">Impostazioni</a>';
        //if not in database
    } else {
        if (!$info['dependencies_unsatisfied']){
            $install.='<a style="color: #C84000;" href="index.php?r=adm/pluginmanager/install'.'&plugin='.$info['name'].'">Installa</a>';
        } else {
            $dependencies = "";
            foreach ($info['dependencies_unsatisfied'] as $k => $v){
                $dependencies .= "\n".$k.": ".$v;
            }
            $install.='<div style="color: grey;cursor: help;" title="Dependencies not satisfied:'.$dependencies.'">Installa</div>';
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

echo $table->getTable();

echo '
<script>
function askUninstall(link){
    if (confirm("'.Lang::t('_PLUGIN_UNINSTALL_CONFIRMATION', 'configuration').'")) {
        location.href=link;
    }
}
</script>
';