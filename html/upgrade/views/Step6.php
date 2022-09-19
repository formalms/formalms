<h2><?php echo Lang::t('_UPGRADING_LANGUAGES'); ?></h2>

<h3><?php echo Lang::t('_LANGUAGES'); ?></h3>

<?php $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession(); ?>

<div id="import_lang_info">
    <?php
    $langInstall = $session->get('lang_install');
    $platformArr = $session->get('platform_arr');
    foreach ($langInstall as $code => $ok) { ?>
        <div><span id="loading_img_<?php echo $code; ?>" style="visibility: hidden;"><img
                        src="<?php echo getTemplatePath(); ?>images/loading.gif" alt="loading"/></span>
            <?php echo ucfirst($code); ?>
        </div>
    <?php } ?>
</div>
<script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function () {
        disableBtnNext(true);
        importLanguages();
    });

    function importLanguages() {

        var language = new Array('<?php echo implode("','", array_keys($langInstall)); ?>');
        var platform = new Array('<?php echo implode("','", array_keys($platformArr)); ?>');

        var prev_lang = '';

        var callback = {
            success: function (o) {
                var res = YAHOO.lang.JSON.parse(o.responseText);
                var next_lang = res['next_lang'];
                var current_lang = res['current_lang'];
                var next_platform = res['next_platform'];

                if (next_lang != current_lang) {
                    YAHOO.util.Dom.get('loading_img_' + current_lang).childNodes[0].src = '../install/templates/standard/images/complete.png';
                    if (next_lang != false) {
                        YAHOO.util.Dom.get('loading_img_' + next_lang).style.visibility = 'visible';
                    }
                }

                if (next_lang != false) {
                    param = "lang=" + next_lang + "&platform=" + next_platform + '&upgrade=1';
                    YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, param);
                } else {
                    disableBtnNext(false);
                }
            },
            error: function (o) {
                disableBtnNext(false);
            }
        };

        var sUrl = './update_lang.php';

        YAHOO.util.Dom.get('loading_img_' + language[0]).style.visibility = 'visible';

        param = "lang=" + language[0] + "&platform=" + platform[0] + '&upgrade=1';
        YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, param);
    }

</script>