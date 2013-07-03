<div id="middlearea" class="yui-navset">
	<ul class="yui-nav">
		<?php if($this->isActive('elearning')) : ?>
		<li<?php echo $this->selected('elearning'); ?>>
			<a href="index.php?r=lms/elearning/show&sop=unregistercourse"><em><?php echo Lang::t('_ELEARNING', 'middlearea'); ?></em><?php echo ( $elearning ? '<b>'.$elearning.'</b>' : '' ); ?></a></li>
		<?php endif; ?>

		<?php if($this->isActive('classroom')) : ?>
		<li<?php echo $this->selected('classroom'); ?>>
			<a href="index.php?r=lms/classroom/show&sop=unregistercourse"><em><?php echo Lang::t('_CLASSROOM', 'middlearea'); ?></em><?php echo ( $classroom ? '<b>'.$classroom.'</b>' : '' ); ?></a></li>
		<?php endif; ?>

		<?php if($this->isActive('catalog')) : ?>
		<li<?php echo $this->selected('catalog'); ?>>
			<a href="index.php?r=lms/catalog/show&sop=unregistercourse"><em><?php echo Lang::t('_CATALOGUE', 'middlearea'); ?></em><?php echo ( $catalog ? '<b>'.$catalog.'</b>' : '' ); ?></a></li>
		<?php endif; ?>

		<?php if($this->isActive('assessment')) : ?>
		<li<?php echo $this->selected('assessment'); ?>>
			<a href="index.php?r=lms/assessment/show&sop=unregistercourse"><em><?php echo Lang::t('_ASSESSMENT', 'menu'); ?></em><?php echo ( $assessment ? '<b>'.$assessment.'</b>' : '' ); ?></a></li>
		<?php endif; ?>

		<?php if($this->isActive('coursepath')) : ?>
		<li<?php echo $this->selected('coursepath'); ?>>
			<a href="index.php?r=lms/coursepath/show&sop=unregistercourse"><em><?php echo Lang::t('_COURSEPATH', 'coursepath'); ?></em><?php echo ( $coursepath ? '<b>'.$coursepath.'</b>' : '' ); ?></a></li>
		<?php endif; ?>

		<?php if($this->isActive('games')) : ?>
		<li<?php echo $this->selected('games'); ?>>
			<a href="index.php?r=lms/games/show&sop=unregistercourse"><em><?php echo Lang::t('_CONTEST', 'middlearea'); ?></em><?php echo ( $games ? '<b>'.$games.'</b>' : '' ); ?></a></li>
		<?php endif; ?>

		<?php if($this->isActive('communication')) : ?>
		<li<?php echo $this->selected('communication'); ?>>
			<a href="index.php?r=lms/communication/show&sop=unregistercourse"><em><?php echo Lang::t('_COMMUNICATIONS', 'middlearea'); ?></em><?php echo ( $communication ? '<b>'.$communication.'</b>' : '' ); ?></a></li>
		<?php endif; ?>

		<?php if($this->isActive('videoconference')) : ?>
		<li<?php echo $this->selected('videoconference'); ?>>
			<a href="index.php?r=lms/videoconference/show&sop=unregistercourse"><em><?php echo Lang::t('_VIDEOCONFERENCE', 'middlearea'); ?></em><?php echo ( $videoconference ? '<b>'.$videoconference.'</b>' : '' ); ?></a></li>
		<?php endif; ?>

		<?php if($this->isActive('kb')) : ?>
		<li<?php echo $this->selected('kb'); ?>>
			<a href="index.php?r=lms/kb/show&sop=unregistercourse"><em><?php echo Lang::t('_CONTENT_LIBRARY', 'middlearea'); ?></em></a></li>
		<?php endif; ?>
	</ul>
	<div class="yui-content">
		<div id="tab_content" class="nested_tab">