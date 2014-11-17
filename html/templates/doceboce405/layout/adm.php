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
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/adm.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/lms-to-review.css" />
		<?php echo Layout::rtl(); ?>
		<!-- specific stylesheet -->

		<!-- printer stylesheet-->
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/print.css" media="print" />
		<?php echo Layout::accessibility(); ?>
		<!-- Page Head area -->
		<?php echo Layout::zone('page_head'); ?>
        <script type="text/javascript">
            YAHOO.util.Event.onDOMReady(function(){
                checkSelect(document.getElementById('course_type').value);

                YAHOO.util.Event.on(
                    YAHOO.util.Selector.query('select#course_type'), 'change', function (e) {
                        checkSelect(this.value);
                });

            });

            function checkSelect(val) {
                if(val == 'elearning') {
                    document.getElementById("auto_subscription").removeAttribute("disabled");
                }
                else {
                    document.getElementById("auto_subscription").disabled = "disabled";
                    document.getElementById("auto_subscription").checked = false;
                }
            }
        </script>
	</head>
	<body class="yui-skin-docebo yui-skin-sam">
		<!-- blind nav -->
		<?php echo Layout::zone('blind_navigation'); ?>
		<!-- feedback -->
		<?php echo Layout::zone('feedback'); ?>
		<!-- header -->
		<div id="header" class="layout_header">
			<?php if(!Docebo::user()->isAnonymous()) : ?>
			<div class="user_panel">
				<p><?php if (!Docebo::user()->isAnonymous()) echo '<b><span>'.Lang::t('_WELCOME', 'profile').', </span>'.Docebo::user()->getUserName().'</b>'; ?><br />
					<?php echo Format::date(date("Y-m-d H:i:s")); ?><br />
					<span class="select-language"><?php echo Layout::change_lang(); ?></span>
				</p>
				<?php if (!Docebo::user()->isAnonymous()): ?>
				<ul>
					<li><a class="identity" href="index.php?r=lms/profile/show">
						<span><?php echo Lang::t('_PROFILE', 'profile'); ?></span>
					</a></li><li>
					<a class="logout" href="index.php?modname=login&amp;op=logout">
						<!-- <img src="<?php echo Layout::path(); ?>images/standard/exit.png" alt="Left logo" />&nbsp; -->
						<span><?php echo Lang::t('_LOGOUT', 'standard'); ?></span>
					</a></li>
				</ul>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<img class="left_logo" src="<?php echo Layout::path(); ?>images/company_logo.png" alt="Left logo" />
			<div class="nofloat"></div>
			<?php echo Layout::zone('header'); ?>
		</div>
		<!-- menu_over -->
		<?php echo Layout::zone('menu_over'); ?>
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
		<!-- end scripts -->
		<?php echo Layout::zone('debug'); ?>
		<!-- def_lang -->
		<?php echo Layout::zone('def_lang'); ?>
		<?php echo Layout::analytics(); ?>
	</body>
</html>