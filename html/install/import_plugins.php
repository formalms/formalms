<?php

include('bootstrap.php');
include _base_.'/db/lib.docebodb.php';
include_once _lib_."/mvc/lib.model.php";
include_once _adm_."/models/PluginmanagerAdm.php";
include_once _lib_."/lib.formaplugin.php";

DbConn::getInstance(false,array(
    'db_type'=>$_SESSION['db_info']['db_type'],
    'db_host'=>$_SESSION['db_info']['db_host'],
    'db_user'=>$_SESSION['db_info']['db_user'],
    'db_pass'=>$_SESSION['db_info']['db_pass'],
    'db_name'=>$_SESSION['db_info']['db_name']
));

$GLOBALS['prefix_fw']='core';
$pg=new PluginmanagerAdm();
$plugins = $_GET['plugins'];
$res="ok";
foreach ($plugins as $plugin=>$core){
    if ($pg->installPlugin($plugin,0,false,$core)){
        if (!$pg->setupPlugin($plugin, 1)){
            $res="error";
            break;
        }
    } else {
        $res="error";
        break;
    }
}
echo $res;