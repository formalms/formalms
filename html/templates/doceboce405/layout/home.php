<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
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
		<!-- blind nav -->
		<?php echo Layout::zone('blind_navigation'); ?>
		<!-- header -->
		<div class="header">
			<div class="select-language">
				<?php echo Lang::t('_CHANGELANG', 'register').': '.Layout::change_lang(); ?>
			</div>
			<h1 id="main_title"><a href="index.php"><?php echo Lang::t('_MAIN_TITLE', 'login'); ?></a></h1>
			<div class="nofloat"></div>
		</div>
		<div class="webcontent">
			<?php echo Layout::zone('content'); ?>
		</div>
		<div class="footer">
			<span class="copyright">
				Powered by <a href="http://www.formalms.org/?versions" onclick="window.open(this.href); return false;">forma.lms <sup>&reg;</sup> Community Edition</a>
			</span>
		</div>
		<!-- def lang -->
		<?php echo Layout::zone('def_lang'); ?>
		<!-- scripts -->
		<?php echo Layout::zone('scripts'); ?>
		<!-- end scripts -->
		<?php echo Layout::zone('debug'); ?>
		<?php echo Layout::analytics(); ?>
	</body>
</html>