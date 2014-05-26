<?php echo getTitleArea(Lang::t('_CONFIGURATION', 'configuration')); ?>
<div class="std_block">

	<div id="global_conf" class="yui-navset">
		<ul class="yui-nav">
		<?php
		while(list($id, $canonical_name) = each($regroup)) {
			echo '<li'.($id == $active_tab?' class="selected"':'').'>'
				.'<a href="#tab_g_'.$id.'">'
				.'<em>'.Lang::t('_'.strtoupper($canonical_name), 'configuration').'</em>'
				.'</a>'
				.'</li>';
		}
		reset($regroup);
		?>
		</ul>
		<div class="yui-content">
			<?php
			while(list($id, $canonical_name) = each($regroup)) {

				// print the tab content
				echo '<div id="tab_g_'.$id.'">'
					.'<h2>'.Lang::t('_'.strtoupper($canonical_name), 'configuration').'</h2>'
					//.'<p>'.Lang::t('_CONF_DESCR_'.$id, 'configuration').'</p>'

					.Form::openForm('conf_option_'.$id, 'index.php?r=adm/setting/save')
					.Form::openElementSpace()
					.Form::getHidden('active_tab_'.$id, 'active_tab', $id);
				switch($id) {
					case SMS_GROUP : {
						$this->render('sms_group', array());
					};
					default: echo '<br />';
				}

				$model->printPageWithElement($id);

				echo Form::closeElementSpace()
					.Form::openButtonSpace()
					.Form::getButton('save_config_'.$id, 'save_config', Lang::t('_SAVE', 'configuration'))
					.Form::getButton('undo_'.$id, 'undo', Lang::t('_UNDO', 'configuration'))
					.Form::closeButtonSpace()
					.Form::CloseForm()
					.'<br />'
					.'</div>';
			}
			?>
		</div>
		<div class="nofloat">&nbsp;</div>
	</div>
</div>
<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function(){
	var targets =  YAHOO.util.Selector.query("span[id^=tt_target]");
	new YAHOO.widget.Tooltip("tooltip_info", {
		context:targets,
		effect:{effect:YAHOO.widget.ContainerEffect.FADE, duration:0.20}
	});
	new YAHOO.widget.TabView('global_conf', {orientation:'left'});
    
    var myEl = document.getElementById('register_type_self');

    YAHOO.util.Event.addListener("register_type_self", "click", function(e){
        if (!confirm('<?php echo addslashes(Lang::t('_CONFIRM_REGISTER_TYPE_SELF', 'configuration')) ?>')){
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
</script>