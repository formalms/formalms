<?php
echo getTitleArea(Lang::t('_PLUGIN_LIST', 'configuration'));
echo '<h2>' . Lang::t('_PLUGIN_UPLOAD', 'configuration') . '</h2><br/><br/>';
echo Form::openForm('plugin_upload', 'index.php?r=adm/pluginmanager/upload', false, false, 'multipart/form-data');
echo Form::getInputFilefield('', 'plugin_file_upload', 'plugin_file_upload', '', '', '');
echo '<br/>';
echo Form::getButton('', 'submit_upload', 'Upload', 'forma-button forma-button--orange-hover', 'style="width: auto; padding-left: 10px; padding-right: 10px;"');
echo Form::closeForm();
?>

<hr/>
<?php include Forma::inc(_lib_ . '/formatable/include.php'); ?>
<div class="feedback-<?php echo $res; ?>"><?php echo $feedback; ?></div>
<table class="table table-striped table-bordered display" style="width:100%" id="plugins"></table>
<script>
function askUninstall(link){
    if (confirm("<?php echo Lang::t('_PLUGIN_UNINSTALL_CONFIRMATION', 'configuration'); ?>")) {
        location.href=link;
    }
}
function askPurge(link){
    if (confirm("<?php echo Lang::t('_PLUGIN_PURGE_CONFIRMATION', 'configuration'); ?>")) {
        location.href=link;
    }
}
$(function() {
    var table = $('#plugins').FormaTable({
        rowId: "plugin_id",  // cambia
        processing: true,
        serverSide: false,
        paging: false,
        columns: [  // definisco le colonne
            { data: 'title', title: '<?php echo Lang::t('_PLUGIN_NAME', 'configuration'); ?>', sortable: true, width: '15%'},
            { data: 'version', title: '<?php echo Lang::t('_PLUGIN_VERSION', 'configuration'); ?>', sortable: true, width: 0 },
            { data: 'author', title: '<?php echo Lang::t('_PLUGIN_AUTHOR', 'configuration'); ?>', sortable: true, width: '10%' },
            { data: 'category', title: '<?php echo Lang::t('_PLUGIN_CATEGORY', 'configuration'); ?>', sortable: true, width: '10%' },
            { data: 'description', title: '<?php echo Lang::t('_PLUGIN_DESCRIPTION', 'configuration'); ?>', sortable: false },
            { title: '<?php echo Lang::t('_PLUGIN_SETTINGS', 'configuration'); ?>', width: '5%', sortable: false, searchable: false, render: function(data, type, row, meta) {
                if (row['plugin_id']){
                    return ' <a class="forma-button forma-button--orange-hover"  href="index.php?r=adm/pluginmanager/showSettings'+'&plugin=' + row['name'] + '"><?php echo Lang::t('_PLUGIN_SETTINGS', 'configuration'); ?></a>';
                }
            }},
            { title: '<?php echo Lang::t('_PLUGIN_INSTALL', 'configuration'); ?>', width: '5%', sortable: false, searchable: false, render: function(data, type, row, meta) {
                $info = row
                $install = ""
                $errors="";
                if ($info['plugin_id']){
                    if ($info['core']==="0"){
                        if (!$info['version_error']){
                          
                                $install+='<a class="forma-button forma-button--orange-hover"  href="javascript:askUninstall(\'index.php?r=adm/pluginmanager/uninstall'+'&plugin=' + $info['name'] + '\');"><?php echo Lang::t('_PLUGIN_UNINSTALL', 'configuration'); ?></a>';
                                if ($info['update']){
                                    $error="";
                                    <?php if (!class_exists('ZipArchive')) { ?>
                                        $error+= '<?php echo Lang::t('_PLUGIN_ERROR_NOT_ONLINE_UPDATE', 'configuration'); ?><br>';
                                    <?php } ?>
                                    $install+= ' <a class="forma-button forma-button--orange-hover" title="' + $error + '" style="color: #006d07;" href="index.php?r=adm/pluginmanager/update' + '&plugin=' + $info['name'] + ($info['online'] ? "&online=true" : "") + '"><?php echo Lang::t('_PLUGIN_UPDATE', 'configuration'); ?></a>';
                                }
                        }else {
                            $errors+= '<a class="forma-button forma-button--orange-hover" style="color: #c80014;" href="javascript:;"><?php echo Lang::t('_PLUGIN_ERROR_OLD_VERSION', 'configuration'); ?></a>';
                        }
                    }
                } else {
                    if (!$info['dependencies_unsatisfied']){
                        $install+='<a class="forma-button forma-button--orange-hover"  href="index.php?r=adm/pluginmanager/install&plugin=' + $info['name'] + '"><?php echo Lang::t('_PLUGIN_INSTALL', 'configuration'); ?></a>';
                    } else {
                        $dependencies = "";
                        for (var $k = 0; $k < $info['dependencies_unsatisfied'].length; $k++) {
                            $dependencies += "\n" + $k + ": " + $info['dependencies_unsatisfied'][$k];
                        }
                        $install+='<div style="color: grey;cursor: help;" title="<?php echo Lang::t('_PLUGIN_ERROR_NO_DEPENDENCIES', 'configuration'); ?>:' + $dependencies + '"><?php echo Lang::t('_PLUGIN_INSTALL', 'configuration'); ?></div>';
                    }
                }
                return $install + $errors
            }},
            { title: '<?php echo Lang::t('_PLUGIN_PURGE', 'configuration'); ?>', width: '80', sortable: false, searchable: false, render: function(data, type, row, meta) {
                if (!row['plugin_id']){
                    return '<a class="forma-button purge-button" href="javascript:askPurge(\'index.php?r=adm/pluginmanager/purge'+'&plugin=' + $info['name'] + '\');"><span class="ico-sprite subs_del"/></a>';
                }
            }},
            { title: '<?php echo Lang::t('_PLUGIN_ACTIVATE', 'configuration'); ?>', width: '5%', sortable: false, searchable: false, render: function(data, type, row, meta) {
                $info = row
                $activate="";
                $dependencies = "";
   
                if ($info['plugin_id']){
                    if ($info['core']==="0"){
                        if (!$info['version_error']){

                           if($info['unsuitable_forma']) {
                               $activate = '<div style="color: grey;cursor: help;" title="<?php echo Lang::t('_PLUGIN_ERROR_ACTIVATE_UNSUITABLE_FORMA', 'configuration'); ?>"><?php echo Lang::t('_PLUGIN_ACTIVATE', 'configuration'); ?></div>';
                               return $activate;
                           }

                            if($info['dependence_of']) {
                                var k = 1;
                                for(const dependency in $info['dependence_of']) {
                                    $dependencies += '\n' + k + ' : ' + dependency ;
                                    k++;
                                }
                                
                            }
                            if ($info['active'] == '1'){
                                $activate =' <a class="forma-button forma-button--orange-hover"  href="index.php?r=adm/pluginmanager/deactivate'+'&plugin=' + $info['name'] + '"><?php echo Lang::t('_PLUGIN_DEACTIVATE', 'configuration'); ?></a>';
                          
                                if($dependencies!="") {
                                    $activate = '<div style="color: grey;cursor: help;" title="<?php echo Lang::t('_PLUGIN_ERROR_DEACTIVATE_DEPENDENCE', 'configuration'); ?> ' + $dependencies + '"><?php echo Lang::t('_PLUGIN_DEACTIVATE', 'configuration'); ?></div>';
                        
                                }
                              
                               
                            } else {
                                $activate =' <a class="forma-button forma-button--orange-hover"  href="index.php?r=adm/pluginmanager/activate'+'&plugin=' + $info['name'] + '"><?php echo Lang::t('_PLUGIN_ACTIVATE', 'configuration'); ?></a>';

                                if($dependencies!="") {
                                    $activate = '<div style="color: grey;cursor: help;" title="<?php echo Lang::t('_PLUGIN_ERROR_ACTIVATE_DEPENDENCE', 'configuration'); ?> ' + $dependencies + '"><?php echo Lang::t('_PLUGIN_ACTIVATE', 'configuration'); ?></div>';
                        
                                }
                         
                            }
                        
                        }
                    }
                }
               
                
                return $activate;
        }},
        { title: '<?php echo Lang::t('_PRIORITY', 'configuration'); ?>', width: '150px', sortable: false, searchable: false, render: function(data, type, row, meta) {
            $info = row
            if ($info['plugin_id']){
                return '' +
                    '<form>' +
                        '<input type="hidden" name="r" value="adm/pluginmanager/set_priority"/>' +
                        '<input type="hidden" name="plugin" value="' + $info['name'] + '"/>' +
                        '<input style="height: 28px; max-width: 70px; font-size: 13px;" class="form-control" type="number" name="priority" value="' + $info['priority'] + '"/>' +
                        '<button style="display: inline-block; width: auto; margin-left: 10px; height: 28px; line-height: 20px; padding-left: 10px; padding-right:10px;" class="forma-button forma-button--orange-hover" submit><?php echo Lang::t('_SET', 'configuration'); ?></button>' +
                    '</form>'+
                '';
            }
        }},
        ],
        ajax: {
            url: "<?php echo Get::rel_path() . '/ajax.adm_server.php?r=pluginmanager/getTableData'; ?>",
            type: "GET"
        },
        order: [[ 0, "asc" ]],
    });
});
</script>
<style>
    #plugins tr td{
        height: 28px;
    }
    #plugins tr td .forma-button {
        display: inline-block; width: auto; margin-left: 10px; height: 28px; line-height: 20px; padding-left: 10px; padding-right:10px;
    }
    #plugins tr td .purge-button {
        background-color: white;
        border: 2px solid #900;
    }
    .feedback-err {
        background-color: #900;
        color: white;
        font-weight: bold;
        padding: 20px;
    }
</style>