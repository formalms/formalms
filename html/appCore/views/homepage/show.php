<div class="std_block">
	<div class="content">
		<?php if(!$under_maintenence) { ?>
			<?php if($this->model->isCatalogToShow()) { ?>
				<div class="homecatalogue">
					<a href="index.php?r=<?php echo _homecatalog_; ?>"><?php echo Lang::t('_CATALOGUE', 'standard'); ?></a>
				</div>
			<?php } ?>
			<div class="login-box">
				<?php if(!$block_attempts) { ?>
					<h2><?php echo Lang::t("_LOGIN", "login"); ?></h2>
					<?php foreach($this->model->getLoginGUI() AS $loginGUI) { echo $loginGUI; } ?>
					<?php if($done) { ?>
						<div>
							<b class="logout"><?php echo $done ?></b>
						</div>
					<?php } ?>
					<?php if($msg) { ?>
						<div id="service_msg">
							<b class="login_failed"><?php echo $msg ?></b>
						</div>
					<?php } ?>
				<?php } else { ?>
					<h3><?php echo Lang::t('_ACCESS_LOCK', 'login'); ?></h3>
					<p><?php echo $block_attempts; ?></p>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
	<div class="footer">
		<div id="link">
			<?php if($this->model->isSelfRegistrationActive() && !$under_maintenence) { ?>
				<a class="first" href="<?php echo Get::rel_path("base") . "/index.php?r=" . _register_; ?>"><?php echo Lang::t('_REGISTER', 'login'); ?></a>
			<?php } ?>
			<a href="<?php echo Get::rel_path("base") . "/index.php?r=" . _lostpwd_; ?>"><?php echo Lang::t("_LOG_LOSTPWD", "login"); ?></a>
		</div>
	</div>
	<div class="external_page">
		<?php
		$external_pages = $this->model->getExternalPages();
		if(!empty($external_pages)) {
			?>
			<ul id="main_menu">
				<?php foreach ($external_pages AS $id_page => $title) { ?>
					<li <?php if($id_page == end(array_keys($external_pages))) { ?>class='last'<?php } ?>>
						<a href="<?php echo Get::rel_path("base") . "/index.php?r=" . _homewebpage_ . "&page=" . $id_page; ?>">
							<?php echo $title ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		<?php } ?>
	</div>
	<div class="webcontent">
		<?php if($under_maintenence) { ?>
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
</div>