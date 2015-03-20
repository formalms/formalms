<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Layout::lang_code(); ?>">
	<head>
	    <!--Fix funzionamento scorm su IE9-->
        <meta http-equiv="x-ua-compatible" content="IE=8"></meta>
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
			<?php if ($GLOBALS['maintenance'] != "on") { ?>
			<div class="select-language">
				<?php echo Lang::t('_CHANGELANG', 'register').': '.Layout::change_lang(); ?>
			</div>
			<?php } ?>
			<!--<h1 id="main_title"><a href="index.php"><?php echo Lang::t('_MAIN_TITLE', 'login'); ?></a></h1>-->
			<a href="index.php"><img class="left_logo" src="<?php echo Layout::path(); ?>images/company_logo.png" alt="Left logo" /></a>
			<div class="nofloat"></div>
		</div>
        <div class="content">
            <?php if ($GLOBALS['framework']['course_block'] == "on" && $GLOBALS['maintenance'] != "on") { ?>
			    <div class="homecatalogue">
				    <?php echo Layout::get_catalogue(); ?>
			    </div>
            <?php } ?>                
			<?php if ($GLOBALS['maintenance'] != "on") { ?>
			<div class="login-box<?php echo LoginLayout::isSocialActive() ? '-social': ''; ?>">
			<h2>LOGIN</h2>
				<?php echo LoginLayout::social_login(); ?>
				<?php echo LoginLayout::login_form(); ?>
				<?php echo LoginLayout::service_msg(); ?>
			</div>
			<?php } ?>
		</div>
              
		<!-- footer -->
		<div class="footer">
			<?php if ($GLOBALS['maintenance'] != "on") { ?>
			<?php echo Layout::zone('footer'); ?>
			<?php echo LoginLayout::links(); ?>
			<?php } ?>
			<div class="copyright">
				<?php echo Layout::copyright(); ?>
			</div>
		</div>
        <div class="external_page"><?php echo LoginLayout::external_page() ?></div>                                                    
		<div class="webcontent">
			<?php if ($GLOBALS['maintenance'] == "on") { ?>
			<div class="box">
				<h3><?php echo Lang::t('_MAINTENANCE', 'configuration'); ?></h3>
				<div class="text">
					<?php echo Lang::t('_MAINTENANCE_TEXT', 'login'); ?>
				</div>
			</div>
			<?php } ?>

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