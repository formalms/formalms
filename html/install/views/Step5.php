<h2><?php echo Lang::t('_TITLE_STEP5'); ?></h2>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);
		YAHOO.util.Event.addListener(['admin_user', 'admin_confpass'], "blur", function(e) {
			validateStep5();
		});
		YAHOO.util.Event.addListener("my_button", "mouseenter", function(e) {
			validateStep5();
		});
		var nodes = YAHOO.util.Selector.query('input[type="checkbox"]');
		YAHOO.util.Event.addListener(nodes, "click", function(e) {
			validateStep5();
		});
	});

	function validateStep5() {
		var err =false;
		var msg = '';
		disableBtnNext(true);

		setInputError('admin_user', false);
		setInputError('admin_pass', false);
		setInputError('admin_confpass', false);

		if ( YAHOO.util.Dom.get('admin_user').value == '') {
			setInputError('admin_user');
			msg += "<?php echo addslashes(Lang::t('_ADMIN_USERID_REQ')) ?>" + "<br>";
			err =true;
		}

		if ( YAHOO.util.Dom.get('admin_pass').value == '') {
			setInputError('admin_pass');
			msg += "<?php echo addslashes(Lang::t('_ADMIN_PASS_REQ')) ?>" + "<br>";
			err =true;
		}

		if ( YAHOO.util.Dom.get('admin_pass').value != YAHOO.util.Dom.get('admin_confpass').value) {
			setInputError('admin_pass');
			setInputError('admin_confpass');
			msg += "<?php echo addslashes(Lang::t('_ADMIN_PASS_DOESNT_MATCH')) ?>" + "<br>";
			err =true;
		}

		var nodes = YAHOO.util.Selector.query('input[type="checkbox"]:checked');
		if ( nodes.length < 1) {
			msg += "<?php echo addslashes(Lang::t('_NO_LANG_SELECTED')) ?>" + "<br>";
			err =true;
		}
		if ( err ) setWarnMsg(msg);

		disableBtnNext(err);
		if (!err) { hideWarnMsg(); }
	}
</script>
<h3><?php echo Lang::t('_ADMIN_USER_INFO'); ?></h3>
<?php
echo Form::getTextfield(Lang::t('_ADMIN_USERNAME'), "admin_user", "adm_info[userid]", 255)
	.Form::getTextfield(Lang::t('_ADMIN_FIRSTNAME'), "admin_firstname", "adm_info[firstname]", 255)
	.Form::getTextfield(Lang::t('_ADMIN_LASTNAME'), "admin_lastname", "adm_info[lastname]", 255)
	.Form::getPassword(Lang::t('_ADMIN_PASS'), "admin_pass", "adm_info[pass]", 255)
	.Form::getPassword(Lang::t('_ADMIN_CONFPASS'), "admin_confpass", "adm_info[confpass]", 255)
	.Form::getTextfield(Lang::t('_ADMIN_EMAIL'), "admin_email", "adm_info[email]", 255);
?>

<h3><?php echo Lang::t('_LANG_TO_INSTALL'); ?></h3>
<ul class="lang_to_install_list">
<?php
foreach(Lang::getLanguageList('language') as $code=>$label) {
	$sel =($code == Lang::getSelLang());
	echo('<li>'.Form::getCheckbox($label, 'lang_'.$code, 'lang_install['.$code.']', 1, $sel).'</li>'."\n");
}
?>
</ul>