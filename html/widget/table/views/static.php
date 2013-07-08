<?php echo ( $caption ? ' <p class="table-caption">'.$caption.'</p>' : '' ); ?>
<?php if (!empty($rel_actions)) { ?>
<div class="table-container-over">
	<div class="table-actions">
		<?php echo is_array($rel_actions) ? $rel_actions[0] : "".$rel_actions; ?>
	</div>
	<div id="<?php echo $id; ?>_pag_over"></div>
	<div class="nofloat"></div>
</div>
<?php } ?>
<!-- Table -->
<div id="<?php echo $id; ?>" class="yui-dt">
	<table class="table-view" <?php echo ( $summary ? ' summary="'.$summary.'"' : '' ); ?> cellspacing="0">
		<?php //echo ( $caption ? ' <caption>'.$caption.'</caption>' : '' ); ?>
		<thead>
			<tr class="yui-dt-first yui-dt-last">
				<?php $first = true; $last = false; $i = 0;
				while(list($key, $row) = each($this->header)) :
					$last = ($i == count($row)-1);  ?>

				<th class="yui-dt-<?php echo ( $first ? 'first' : ($last ? 'last' : '' ) ).( !empty($this->styles[$key]) ? ' '.$this->styles[$key] : '' ); ?>; ?>">
					<div class="yui-dt-liner">
						<span class="yui-dt-label"><?php echo $row; ?></span>
					</div>
				</th>
				
				<?php $i++; $first = false;
				endwhile; ?>
			</tr>
		</thead>
		<tbody>
			<?php $i = 0;
			while(list($key, $row) = each($this->data)) :?>
			<tr class="yui-dt-<?php echo ($i++%2?'odd':'even'); ?>">
				
				<?php $first = true; $j = 0;
				while(list($key, $cell) = each($row)) :
					$last = ($j == count($row)-1); ?>

				<td class="yui-dt-<?php echo ( $first ? 'first' : ($last ? 'last' : 'filter' ) ).( !empty($this->styles[$key]) ? ' '.$this->styles[$key] : '' ); ?>">
					<div class="yui-dt-liner">
						<span class="yui-dt-label"><?php echo $cell; ?></span>
					</div>
				</td>

				<?php $j++; $first = false;
				endwhile; ?>
			</tr>
			<?php endwhile; ?>
		</tbody>
	</table>
</div>
<?php if (!empty($rel_actions)) { ?>
<div class="table-container-below">
	<div class="table-actions">
		<?php echo is_array($rel_actions) ? $rel_actions[1] : "".$rel_actions; ?>
	</div>
	<div id="<?php echo $id; ?>_pag_below"></div>
	<div class="nofloat"></div>
</div>
<?php } ?>