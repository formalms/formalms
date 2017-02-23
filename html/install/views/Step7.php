<h2><?php echo Lang::t('_TITLE_STEP7'); ?></h2>
<div id="data_container">
    <h3>This plugins will be installed because are part of the core of forma.lms</h3>
    <?php echo Step7Controller::plugin_core(); ?>
    <h3>Select from this list the plugins you want to install</h3>
    <?php echo Step7Controller::plugin_list(); ?>
    <input type="button" onclick="install_plugin();" value="Install">
</div>
<script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function() {
        disableBtnNext(true);
    });
    function install_plugin() {
        var plugin=document.getElementsByTagName("input")
        var url=""
        for (var x=0; x<plugin.length; x++){
            if (plugin[x].checked){
                var core=0;
                if (plugin[x].style.display=="none"){
                    core=1;
                }
                url+="plugins["+plugin[x].value+"]="+core+"&"
            }
        }
        var sUrl ='import_plugins.php?'+url;
        YAHOO.util.Connect.asyncRequest('GET', sUrl, {
            success:function (o) {
                if (o.responseText=="ok"){
                    document.getElementById('data_container').innerHTML="<h3>Installation successful, please proceed.<h3>"
                    hideWarnMsg();
                    disableBtnNext(false);
                } else {
                    setWarnMsg(o.responseText);
                    disableBtnNext(true);
                }
            },
            failure: function(o) {
                setWarnMsg("Fatal error during plugin installation, clean database and retry.");
                disableBtnNext(true);
            }
        });
    }

</script>