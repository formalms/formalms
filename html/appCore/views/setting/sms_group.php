<?php
require_once(Docebo::inc(_adm_ . '/lib/Sms/SmsGatewayManager.php'));
$credit = SmsGatewayManager::getCredit();
?>
<div class="container-smsmarket">
<!--	<p>-->
<!--		<a href="http://www.skebby.it/" onclick="window.open(this.href); return false;" title="--><?php //echo Lang::t('_SKEBBY_LOGO', 'configuration'); ?><!--">-->
<!--			<img src="--><?php //echo getPathImage(); ?><!--config/smsmarket.gif" alt="--><?php //echo Lang::t('_SMSMARKET_LOGO', 'configuration'); ?><!--" title="--><?php //echo Lang::t('_SMSMARKET_LOGO', 'configuration'); ?><!--" />-->
<!--		</a>-->
<!--	</p>-->
	<p>
		<b><?php echo Lang::t('_SMS_CREDIT', 'configuration').': '.$credit['credit_left']; ?></b><br>
		<b><?php echo Lang::t('_SMS_CREDIT', 'configuration').': '.$credit[SkebbySmsGateway::SMS_TYPE_BASIC]; ?></b><br>
		<b><?php echo Lang::t('_SMS_CREDIT', 'configuration').': '.$credit[SkebbySmsGateway::SMS_TYPE_CLASSIC]; ?></b>
	</p>
	<p>
		<a href="http://www.skebby.it/" onclick="window.open(this.href); return false;"><?php echo Lang::t('_SMS_BUY_RECHARGE', 'configuration'); ?></a>
	</p>
</div>