<?php
require_once(_base_.'/lib/lib.table.php');

echo getTitleArea(Lang::t('_PLUGIN_LIST', 'configuration'));

$table = new Table(0, Lang::t('_PLUGIN_LIST', 'configuration'), Lang::t('_PLUGIN_LIST', 'configuration'));

$table->setTableId("table_plugin");

$cont_h = array(
    "Nome",
    "Versione",
    "Autore",
    "Descrizione",
    "Azioni"
);

$type_h = array('', 'align_center', 'align_center', '', '');

$table->setColsStyle($type_h);

$table->addHead($cont_h);

foreach ($plugins as $info){
    $actions="";
    //if already in database
    if (isset($info['plugin_id'])){
        if (!$info['version_error']){
            if (!$info['update']){
                $actions.='<a style="color: #C84000;" href="index.php?r=adm/pluginmanager/uninstall'.'&plugin='.$info['name'].'">Disinstalla</a>';
                //if active
                if ($info['active']=="1"){
                    $actions.=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/deactivate'.'&plugin='.$info['name'].'">Disattiva</a>';
                    //if not active
                } else {
                    $actions.=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/activate'.'&plugin='.$info['name'].'">Attiva</a>';
                }
            } else {
                if (!class_exists('ZipArchive')){
                    $actions.= "You can't use the online feautures<br>";
                }
                $actions.= '<a style="color: #006d07;" href="index.php?r=adm/pluginmanager/update'.'&plugin='.$info['name'].'&online='.$info['online'].'">Update</a>';
            }
        } else {
            $actions.= '<a style="color: #c80014;" href="javascript:;">Versione più vecchia</a>';
        }
        //if not in database
    } else {
        $actions.='<a style="color: #C84000;" href="index.php?r=adm/pluginmanager/install'.'&plugin='.$info['name'].'">Installa</a>';
    }
    $actions.=' <a style="color: #C84000;" href="index.php?r=adm/pluginmanager/showSettings'.'&plugin='.$info['name'].'">Impostazioni</a>';
    $table->addBody(array(
        $info['title'],
        $info['version'],
        $info['author'],
        $info['description'],
        $actions
    ));
}

echo $table->getTable();