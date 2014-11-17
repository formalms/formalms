<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Layout::lang_code(); ?>">
	<head>
		<title><?php echo Layout::title(); ?></title>
		<link rel="shortcut icon" href="<?php echo Layout::path(); ?>images/favicon.png" type="image/png" />
		<?php echo Layout::zone('meta'); ?>
		<!-- reset and font stylesheet -->
		<?php echo Layout::resetter(); ?>
		<!-- common stylesheet -->
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/base.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/lms-home.css" />
		<?php echo Layout::rtl(); ?>
		<!-- specific stylesheet -->
		<?php YuiLib::load('base'); ?>
		<!-- printer stylesheet-->
		<?php echo Layout::accessibility(); ?>
		<!-- Page Head area -->
		<?php echo Layout::zone('page_head'); ?>
	</head>
	<body class="yui-skin-docebo yui-skin-sam">

		<div class="header">
			<div class="select-language">
				<?php echo Lang::t('_CHANGELANG', 'register').': '.Layout::change_lang(); ?>
			</div>
			<h1 id="main_title"><a href="index.php"><?php echo Lang::t('_MAIN_TITLE', 'login'); ?></a></h1>
			<div class="nofloat"></div>
		</div>
		<div class="content">
			<!-- <div class="homecatalogue">
				<?php echo Layout::get_catalogue(); ?>
			</div> -->
			<h2>E-LEARNING LOGIN</h2>
			<div class="login-box<?php echo LoginLayout::isSocialActive() ? '-social': ''; ?>">
				<?php echo LoginLayout::social_login(); ?>
				<?php echo LoginLayout::login_form(); ?>
			</div>
			<?php echo LoginLayout::service_msg(); ?>
		</div>
		<div class="footer">
			<?php echo LoginLayout::links(); ?>
			<span class="copyright">
				Powered by <a href="http://www.formalms.org/?versions" onclick="window.open(this.href); return false;">forma.lms <sup>&reg;</sup> Community Edition</a>
			</span>
		</div>
		<div class="webcontent">
			<div class="box">
				<h3><?php echo Lang::t('_HOMEPAGE', 'login'); ?></h3>
				<div class="text">
					<?php echo Lang::t('_INTRO_STD_TEXT', 'login'); ?>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			window.onload = function(){try{window.document.getElementById('login_userid').focus(); } catch(e){}}
		</script>
		<?php echo Layout::analytics(); ?>
	</body>
</html>