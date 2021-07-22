<h2><?php echo Lang::t('_TITLE_STEP8'); ?></h2>

<?php echo Lang::t('_INSTALLATION_COMPLETED'); ?>

<?php if (!$_SESSION['config_saved']): ?>
<h3 style="color: red;"><?php echo Lang::t('_CONFIG_FILE_NOT_SAVED'); ?></h3>
<ul class="info">
	<li><a href="download_conf.php"><?php echo Lang::t('_DOWNLOAD_CONFIG'); ?></a></li>
</ul>
<?php endif; ?>


<h3><?php echo Lang::t('REMOVE_INSTALL_FOLDER'); ?></h3><br>


<h3><?php echo Lang::t('_INSTALLATION_DETAILS'); ?>:</h3>
<ul class="info">
	<li><?php echo Lang::t('_SITE_HOMEPAGE'); ?>: <a href="<?php echo $_SESSION['site_url']; ?>" target="_blank"><?php echo $_SESSION['site_url']; ?></a></li>
	<li><?php echo Lang::t('_ADMIN_USERNAME'); ?>: <?php echo $_SESSION['adm_info']['userid']; ?></li>
	<li>
		<?php echo Lang::t('_ADMIN_PASS'); ?>: <span id="pwd">*******</span>
		[ <a href="#" onclick="YAHOO.util.Dom.get('pwd').innerHTML ='<?php echo $_SESSION['adm_info']['pass']; ?>'; return false;"><?php echo Lang::t('_REVEAL_PASSWORD'); ?></a> ]
	</li>
</ul>
<h3><?php echo Lang::t('_USEFUL_LINKS'); ?>:</h3>

<ul class="info">
<li><a href="http://www.formalms.org" target="_blank">Official Website</a></li>
</ul>
<br />
<h3><?php echo Lang::t('_COMMUNITY'); ?>:</h3>
<ul class="info">
<li><a href="http://www.formalms.org/community" target="_blank">Community</a></li>
<br />
<li><a href="http://sourceforge.net/projects/forma/" target="_blank">SourceForge Project</a></li>
<li><a href="https://www.linkedin.com/company/formalms" target="_blank">Linkedin Project Page</a></li>
</ul>
<br />
<h3><?php echo Lang::t('_COMMERCIAL_SERVICES'); ?>:</h3>
<ul class="info">
<li><a href="http://www.formalms.org/about/partners.html" target="_blank">Partners</a></li>
</ul>

<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function() {
	YAHOO.util.Dom.get('my_button').style.visibility ='hidden';
});
</script>