<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Layout::lang_code(); ?>">
	<head>
		<title><?php echo Layout::title(); ?></title>
		<?php echo Layout::zone('meta'); ?>
		<link rel="shortcut icon" href="<?php echo Layout::path(); ?>images/favicon.png" type="image/png" />
		<!-- reset and font stylesheet -->
		<?php echo Layout::resetter(); ?>
		<!-- common stylesheet -->
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/base.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/lms.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/lms-to-review.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/lms-menu.css" />
		<!-- specific stylesheet -->

		<!-- printer stylesheet-->
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/print.css" media="print" />
		<?php echo Layout::accessibility(); ?>
		<!-- Page Head area -->
		<script type="text/javascript" src="<?php echo Get::rel_path('base'); ?>/lib/js_utils.js"></script>
		<?php echo Layout::zone('page_head'); ?>
		<?php echo Layout::rtl(); ?>
	</head>
	<body class="yui-skin-docebo yui-skin-sam">
		<!-- blind nav -->
		<?php echo Layout::zone('blind_navigation'); ?>
		<!-- feedback -->
		<?php echo Layout::zone('feedback'); ?>
		<!-- header -->
		<div id="header" class="layout_header">

			<div class="user_panel">
				<p><?php if (!Docebo::user()->isAnonymous()) echo '<b><span>'.Lang::t('_WELCOME', 'profile').', </span>'.Docebo::user()->getUserName().'</b>'; ?><br />
					<?php echo Format::date(date("Y-m-d H:i:s")); ?><br />
					<span class="select-language"><?php echo Layout::change_lang(); ?></span>
				</p>
				<?php if (!Docebo::user()->isAnonymous()): ?>
				<ul>
					<li><a class="identity" href="index.php?r=profile/show">
						<!-- <img src="<?php echo Layout::path(); ?>images/standard/identity.png" alt="" />&nbsp; -->
						<span><?php echo Lang::t('_PROFILE', 'profile'); ?></span>
					</a></li><li>
					<a class="logout" href="index.php?modname=login&amp;op=logout">
						<!-- <img src="<?php echo Layout::path(); ?>images/standard/exit.png" alt="Left logo" />&nbsp; -->
						<span><?php echo Lang::t('_LOGOUT', 'standard'); ?></span>
					</a></li>
				</ul>
				<?php endif; ?>
			</div>
			<img class="left_logo" src="<?php echo Layout::path(); ?>images/company_logo.png" alt="Left logo" />
			<?php echo Layout::zone('header'); ?>
			<div class="nofloat"></div>
		</div>
		<!-- menu_over -->
		<div id="menu_over" class="layout_menu_over">
			<?php echo Layout::cart(); ?>
			<?php echo Layout::zone('menu_over'); ?>
		</div>
		<!-- content -->
		<div class="layout_colum_container">
			<?php echo Layout::zone('content'); ?>
			<div class="nofloat"></div>
		</div>
		<!-- footer -->
		<div id="footer" class="layout_footer">
			<?php echo Layout::zone('footer'); ?>
			<p class="powered_by">
				Powered by <a href="http://www.formalms.org/?versions" onclick="window.open(this.href); return false;">forma.lms <sup>&reg;</sup> Community Edition</a>
			</p>
			<div class="nofloat"></div>
		</div>
		<!-- scripts -->
		<?php echo Layout::zone('scripts'); ?>
		<!-- debug -->
		<?php echo Layout::zone('debug'); ?>
		<!-- def_lang -->
		<?php echo Layout::zone('def_lang'); ?>
		<?php echo Layout::analytics(); ?>
	</body>
</html>