<?php echo getTitleArea(Lang::t('_CONFIGURATION', 'configuration')); ?>
<div class="std_block">

	<div id="global_conf" class="tabs-wrapper">
		<ul class="nav nav-tabs bordered">
			<li class="dropdown">
				<a href="javascript:void(0)" class="dropdown-toggle" role="button" data-toggle="dropdown">
          <span class="dropdown-title"><?php echo Lang::t('_OTHER_OPTION', 'course'); ?></span> &nbsp;<span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left tabs-dropdown">
				<?php
				while(list($id, $canonical_name) = each($regroup)) {
					// echo '<li'.($id == $active_tab?' class="selected"':'').'>'
					echo '<li class="'.($id == $active_tab ? ' active' : '').'"">'
						.'<a data-toggle="tab" href="#tab_g_'.$id.'">'
						.'<em>'.Lang::t('_'.strtoupper($canonical_name), 'configuration').'</em>'
						.'</a>'
						.'</li>';
				}
				reset($regroup);
				?>
				</ul>
			</li>
		</ul>
		<div class="tab-content">
			<?php
			while(list($id, $canonical_name) = each($regroup)) {

				// print the tab content
				echo '<div class="tab-pane'.($id == $active_tab ? ' active' : '').'" id="tab_g_'.$id.'">'
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
$(function(){
    // check on page reload
    if ($("#home_page_option").val() == 'catalogue'){
        $("#on_usercourse_empty_on").attr("disabled", true);
    }
})

$( "#home_page_option" ).change(function() {
   if ($( this ).val() == 'catalogue') {
      $("#on_usercourse_empty_on").prop('checked', false)
      $("#on_usercourse_empty_on").attr("disabled", true);
   } else {
       $("#on_usercourse_empty_on").attr("disabled", false);
   }
});
</script>