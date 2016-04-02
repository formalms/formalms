<?php
require_once(Docebo::inc(_adm_ . '/lib/Sms/SmsGatewayManager.php'));
try {
    $credit = SmsGatewayManager::getCredit();
} catch (SmsGatewayException $e) {
    $credit = null;
}
?>
<div class="container-smsmarket">
    <!--	<p>-->
    <!--		<a href="http://www.skebby.it/" onclick="window.open(this.href); return false;" title="-->
    <?php //echo Lang::t('_SKEBBY_LOGO', 'configuration'); ?><!--">-->
    <!--			<img src="--><?php //echo getPathImage(); ?><!--config/smsmarket.gif" alt="-->
    <?php //echo Lang::t('_SMSMARKET_LOGO', 'configuration'); ?><!--" title="-->
    <?php //echo Lang::t('_SMSMARKET_LOGO', 'configuration'); ?><!--" />-->
    <!--		</a>-->
    <!--	</p>-->
    <p>
        <?php
        if ($credit != null) {
            ?>
            <b><?php echo Lang::t('_SMS_CREDIT', 'configuration') . ': &euro; ' . round($credit['credit_left'], 2); ?></b><br>
            <b><?php echo Lang::t('_SMS_CREDIT', 'configuration') . ' BASIC: ' . $credit[SkebbySmsGateway::SMS_TYPE_BASIC]; ?></b>
            <br>
            <b><?php echo Lang::t('_SMS_CREDIT', 'configuration') . ' CLASSIC: ' . $credit[SkebbySmsGateway::SMS_TYPE_CLASSIC]; ?></b>
            <?php
        }
        ?>
    </p>

    <p>
        <a href="http://www.skebby.it/"
           onclick="window.open(this.href); return false;"><?php echo Lang::t('_SMS_BUY_RECHARGE', 'configuration'); ?></a>
    </p>
</div>