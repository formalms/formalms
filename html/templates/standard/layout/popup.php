<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Layout::lang_code(); ?>">
	<head>
	    <!--Fix funzionamento scorm su IE9-->
        <meta http-equiv="x-ua-compatible" content="IE=9"></meta>
        <!--END -->

		<title><?php echo Layout::title(); ?></title>
		<?php echo Layout::zone('meta'); ?>
		<?php echo Layout::meta(); ?>
		<link rel="shortcut icon" href="<?php echo Layout::path(); ?>images/favicon.png" type="image/png" />
		<link rel="shortcut icon" href="<?php echo Layout::path(); ?>images/favicon.ico" />
		<!-- reset and font stylesheet -->
		<?php echo Layout::resetter(); ?>
		<!-- common stylesheet -->
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/base.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/adm.css" />
		<?php echo Layout::rtl(); ?>
		<!-- specific stylesheet -->

		<!-- printer stylesheet-->
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/print.css" media="print" />
		<?php echo Layout::accessibility(); ?>
		<!-- Page Head area -->
		<?php echo Layout::zone('page_head'); ?>
	</head>
	<body class="yui-skin-docebo yui-skin-sam">
		<!-- blind nav -->
		<?php echo Layout::zone('blind_navigation'); ?>
		<!-- content -->
		<div class="layout_colum_container">
			<?php echo Layout::zone('content'); ?>
			<div class="nofloat"></div>
		</div>
		<!-- scripts -->
		<?php echo Layout::zone('scripts'); ?>
		<!-- end scripts -->
		<?php echo Layout::zone('debug'); ?>
	</body>
</html>