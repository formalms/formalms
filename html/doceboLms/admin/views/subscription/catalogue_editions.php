<?php echo getTitleArea($page_title_arr); ?>
<div class="std_block">
	<p><?php echo Lang::t('_SELECTED', 'subscribe').': '.$num_users_selected; ?></p>
	<br />
	<?php
		echo Form::openForm('editions_list', 'index.php?r='.$this->link.'/cataloguesubscribesave');

		echo Form::getHidden('id_catalogue', 'id_catalogue', $id_catalogue);
		echo Form::getHidden('user_selection', 'user_selection', str_replace('"', '', $_sel_users) );

		if ($tables['editions'] !== false) echo $tables['editions']->getTable();
		if ($tables['editions'] !== false && $tables['classrooms'] !== false) echo '<br /><br />';
		if ($tables['classrooms'] !== false) echo $tables['classrooms']->getTable();

		echo Form::openButtonSpace();
		echo Form::getButton('save', 'save', Lang::t('_SUBSCRIBE', 'subscribe'));
		echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'));
		echo Form::closeButtonSpace();

		echo Form::closeForm();
	?>
</div>