<?php echo getTitleArea(Lang::t('_CONFIGURATION', 'configuration')); ?>
<div class="std_block">
	<div id="global_conf" class="tabs-wrapper">
		<ul class="nav nav-tabs"><?php
			while(list($id, $canonical_name) = each($regroup)) {?>
				<li class="<?php echo ($id == $active_tab ? ' active' : '');?>">
					<a data-toggle="tab" href="#tab_g_<?php echo $id;?>" class="nav-link">
						<em><?php echo Lang::t('_'.strtoupper($canonical_name), 'configuration');?></em>
					</a>
				</li><?php
			}
			reset($regroup);?>
		</ul>
		<div class="tab-content"><?php
			while(list($id, $canonical_name) = each($regroup)) {

				// print the tab content
				echo '<div class="tab-pane'.($id == $active_tab ? ' active' : '').'" id="tab_g_'.$id.'">'
					.'<h2>'.Lang::t('_'.strtoupper($canonical_name), 'configuration').'</h2>'

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
			}?>
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
