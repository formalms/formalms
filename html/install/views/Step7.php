<h2><?php echo Lang::t('_TITLE_STEP7'); ?></h2>
<?php $php_conf = ini_get_all();

$localCfg = [
	'use_smtp_database' => '',
	'use_smtp' => '',
	'smtp_host' => '',
	'smtp_port' => '',
	'smtp_secure' => '',
	'smtp_auto_tls' => '',
	'smtp_user' => '',
	'smtp_pwd' => '',
];
?>

<script type="text/javascript">


    YAHOO.util.Event.onDOMReady(function () {
        disableBtnNext(true);

        var check_fields = <?php echo json_encode(array_keys($localCfg)); ?>;

        validateInput(check_fields, 'final_check');
        YAHOO.util.Event.addListener("my_button", "mouseenter", function (e) {
            for (i = 0; i < check_fields.length; i++) {
                setInputError(check_fields[i], false);
            }
            validateInput(check_fields, 'final_check');
        });


        YAHOO.util.Event.addListener(check_fields, "blur", function (e) {
            for (i = 0; i < check_fields.length; i++) {

                setInputError(check_fields[i], false);
            }
            validateInput(check_fields);
        });
    });
</script>

<h3><?php echo Lang::t('_SMTP_INFO'); ?></h3>
<div class="form_line_l">
	<?php

	if (file_exists(_base_ . '/config.php')) {
		define('IN_FORMA', true);
		include _base_ . '/config.php';

		foreach ($localCfg as $key => $value) {
			$localCfg[$key] = $cfg[$key];
		}
	}

	$smtptoDB = $cfg['use_smtp_database'];

	$select = [
		'on' => 'Si',
		'off' => 'No'
	];

	$secureSelect = ['ssl' => 'SSL', 'tls' => 'TLS'];

	?>
</div>
<?php
foreach ($localCfg as $key => $value) {

	switch ($key) {
		case 'use_smtp_database':
		case 'use_smtp':
		case 'smtp_auto_tls':
			echo '<div class="form_line_l"><p><label class="floating" for="smtp_info">' . Lang::t('_' . strtoupper($key)) . '</label></p>' . Form::getInputDropdown('', $key, "smtp_info[$key]", $select, $localCfg[$key]) . '</div>';
			break;
		case 'smtp_secure':
			echo '<div class="form_line_l"><p><label class="floating" for="smtp_info">' . Lang::t('_' . strtoupper($key)) . '</label></p>' . Form::getInputDropdown('', $key, "smtp_info[$key]", $secureSelect, $localCfg[$key]) . '</div>';
			break;
		case 'smtp_pwd':
			echo Form::getPassword(Lang::t('_' . strtoupper($key)), $key, "smtp_info[$key]", 255, '', '', '', $value);
			break;
		default:
			echo Form::getTextfield(Lang::t('_' . strtoupper($key)), $key, "smtp_info[$key]", 255, $value);
			break;
	}
}
?>
