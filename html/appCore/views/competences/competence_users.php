<?php
echo getTitleArea(array(
	'index.php?r=adm/competences/show' => Lang::t('_COMPETENCES', 'competences'),
	Lang::t('_COMPETENCE_VIEW_USERS', 'competences')
));
?>
<div class="std_block">
<?php echo Form::openForm('go_back', 'index.php?r=adm/competences/view_competence_report'); ?>
<?php echo Form::getHidden('id', 'id', $id_competence); ?>
<div class="quick_search_form">
	<div>
		<!-- <div class="common_options"></div> -->
		<div class="simple_search_box" id="competences_simple_filter_options" style="display: block;">
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
	</div>
</div>
<br />
<?php echo Form::closeForm(); ?>
<?php
echo $table->getTable();
echo Form::openForm('go_back', 'index.php?r=adm/competences/show');
echo Form::openButtonSpace();
echo Form::getButton('close', 'close', Lang::t('_CLOSE', 'standard'));
echo Form::closeButtonSpace();
echo Form::closeForm();
?>
</div>
<script type="text/javascript">
</script>