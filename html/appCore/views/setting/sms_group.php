<?php
$sms_credit = Get::sett('sms_credit', 0);
if($sms_credit == 0) {
	$credit_left = '0';
	$note = '('.Lang::t('_SMS_CREDIT_UPDATE', 'configuration').')';
} else {
	$credit_left = number_format($sms_credit/1000, 2, ',', '').' &euro;';
	$note = '';
}
?>
<div class="container-smsmarket">
	<p>
		<a href="http://www.smsmarket.it/" onclick="window.open(this.href); return false;" title="<?php echo Lang::t('_SMSMARKET_LOGO', 'configuration'); ?>">
			<img src="<?php echo getPathImage(); ?>config/smsmarket.gif" alt="<?php echo Lang::t('_SMSMARKET_LOGO', 'configuration'); ?>" title="<?php echo Lang::t('_SMSMARKET_LOGO', 'configuration'); ?>" />
		</a>
	</p>
	<p>
		<b><?php echo Lang::t('_SMS_CREDIT', 'configuration').': '.$credit_left; ?></b> <?php echo $note; ?>
	</p>
	<p>
		<a href="http://www.smsmarket.it/acquista_sms.php" onclick="window.open(this.href); return false;"><?php echo Lang::t('_SMS_BUY_RECHARGE', 'configuration'); ?></a>
	</p>
</div>