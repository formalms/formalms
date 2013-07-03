<div class="list_block">
	<h3 class="heading"><?php echo $title; ?></h3>
	<p class="content">
		<?php echo Get::img('standard/report32.png', '', '', 'style="float:left; padding:0 4px 0 0"'); ?>
		<?php echo $description; ?>
	</p>
	<div class="actions">
	<?php
		echo Form::openForm('form_'.$id, $url);
		echo Form::openButtonSpace();
		if (isset($users)) {
			echo Form::getInputDropdown(Lang::t('_USERS', 'standard'), 'dropdown_'.$id, 'id_user', $users['list'], $users['selected'], '');
		}
		echo Form::getButton('button_'.$id, 'button_'.$id, Lang::t('_SHOW', 'standard'));
		echo Form::closeButtonSpace();
		echo Form::closeForm();
	?>
	</div>
	<div class="nofloat"></div>
</div>