<?php
	echo	$nav_bar->getNavBar()
			.$html
			.$nav_bar->getNavBar();
			// #1995 Grifo multimedia LR
?>

<script type="text/javascript">
    var lb = new LightBox();
    lb.back_url = 'index.php?r=lms/catalog/show&sop=unregistercourse';
    
    var Config = {};
    Config.langs = {_CLOSE: '<?php echo Lang::t('_CLOSE', 'standard'); ?>'};
    lb.init(Config);  
</script>