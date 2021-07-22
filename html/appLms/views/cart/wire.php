<?php Get::title(array(Lang::t('_WIRE_INFO', 'cart'))); ?>

<div class="std_block">
	<h3><?php echo Lang::t('_ORDER_NUMBER', 'cart').' '.$transaction_info['id_trans']; ?></h3>
	<br/>
	<?php echo Lang::t('_TOTAL', 'cart'); ?> : <?php echo $total_price; ?> <?php echo (Get::sett('currency_symbol') !== '' ? Get::sett('currency_symbol') : '&eur;'); ?>
	<br/>
	<br/>
	<?php echo Lang::t('_WIRE_PAYMENT_DETAILS', 'cart'); ?>
</div>