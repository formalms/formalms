<?php
		echo getTitleArea(Lang::t('_ORG_CHART_IMPORT_USERS', 'organization_chart'), 'directory_group');
		echo '<div class="std_block">';
		echo getBackUi('index.php?r='.$this->link.'/show_users&id='.$id_group, Lang::t('_BACK', 'standard'));
?>

<table>
	<tr>
		<td><?php echo Lang::t('_TOTAL', 'standard'); ?>:</td>
		<td><?php echo $info['total']; ?></td>
	</tr>
	<tr>
		<td><?php echo Lang::t('_INSERTED', 'standard'); ?>:</td>
		<td><?php echo $info['inserted']; ?></td>
	</tr>
	<tr>
		<td><?php echo Lang::t('_DUPLICATED', 'standard'); ?>:</td>
		<td><?php echo $info['duplicated']; ?></td>
	</tr>
	<tr>
		<td><?php echo Lang::t('_NOT_INSERTED', 'standard'); ?>:</td>
		<td><?php echo $info['not_inserted']; ?></td>
	</tr>
</table>

<?php
		echo getBackUi('index.php?r='.$this->link.'/show_users&id='.$id_group, Lang::t('_BACK', 'standard'));
		echo '</div>';
?>