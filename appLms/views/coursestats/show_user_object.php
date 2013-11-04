<?php
$base_url = 'index.php?r=coursestats/show';
$user_url = 'index.php?r=coursestats/show_user&id_user='.(int)$id_user;
$back_url = $from_user ? $user_url : $base_url;
echo getTitleArea(array(
	$base_url => Lang::t('_COURSESTATS', 'menu_course'),
	$user_url => $info->userid,
	$info->LO_name
));
?>
<div class="std_block">
<?php if (isset($result_message)) echo $result_message; ?>
<?php echo getBackUi($back_url, Lang::t('_BACK', 'standard')); ?>
	<table style="width:100%">
		<tr>
			<td colspan="1"><?php echo '<b>'.Lang::t('_USERNAME', 'standard').'</b>: '.$info->userid; ?></td>
			<td colspan="2"><?php echo '<b>'.Lang::t('_NAME', 'standard').'</b>: '.$info->firstname.' '.$info->lastname; ?></td>
		</tr>
		<tr>
			<td><?php echo '<b>'.Lang::t('_STATUS', 'course').'</b>: '.$info->course_status; ?></td>
			<td><?php echo '<b>'.Lang::t('_DATE_FIRST_ACCESS', 'course').'</b>: '.$info->course_first_access; ?></td>
			<td><?php echo '<b>'.Lang::t('_COMPLETED', 'course').'</b>: '.$info->course_date_complete; ?></td>
			<!--<td><?php echo '<b>'.Lang::t('_DATE_LAST_ACCESS', 'course').'</b>: '.$info->course_last_access; ?></td>-->
		</tr>
	</table>
	<br />
	<div style="border-top:1px solid #000000;"></div>
	<br />
	<table style="margin-left:auto; margin-right:auto;width:50%">
		<tr>
			<td><?php echo '<b>'.Lang::t('_NAME', 'standard').'</b>: '.$info->LO_name; ?></td>
			<td><?php echo '<b>'.Lang::t('_DATE_FIRST_ACCESS', '').'</b>: '.$info->first_access; ?></td>
		</tr>
		<tr>
			<td><?php echo '<b>'.Lang::t('_TYPE', '').'</b>: '.$info->LO_type; ?></td>
			<td><?php echo '<b>'.Lang::t('_DATE_LAST_ACCESS', '').'</b>: '.$info->last_access; ?></td>
		</tr>
		<tr>
			<td><?php echo '<b>'.Lang::t('_STATUS', '').'</b>: '.$info->status; ?></td>
			<td><?php echo '<b>'.Lang::t('_DATE_FIRST_COMPLETE', '').'</b>: '.$info->first_complete; ?></td>
		</tr>
		<tr>
			<td><?php echo '<b>'.Lang::t('_SCORE', '').'</b>: '.$info->score; ?></td>
			<td><?php echo '<b>'.Lang::t('_DATE_LAST_COMPLETE', '').'</b>: '.$info->last_complete; ?></td>
		</tr>
	</table>
	<br />
	<br />
	<div class="align-right">
		<?php
			if ($tracked) {
				$_url = 'index.php?r=coursestats/reset&amp;id_user='.(int)$id_user.'&amp;id_lo='.(int)$id_lo;
				$_title = Lang::t('_RESET', 'course');
				echo '<a href="'.$_url.'" title="'.$_title.'" class="ico-wt-sprite subs_cancel"><span>'.$_title.'</span></a>';
			}
		?>
	</div>
	<br />
	<br />
<?php
if (method_exists($object_lo, 'loadReport'))
	echo $object_lo->loadReport($id_user, true);
?>
<?php echo getBackUi($back_url, Lang::t('_BACK', 'standard')); ?>
</div>