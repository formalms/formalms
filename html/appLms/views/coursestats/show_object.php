<?php
$base_url = 'index.php?r=coursestats/show';
echo getTitleArea(array(
	$base_url => Lang::t('_COURSESTATS', 'menu_course'),
	$info->LO_name
));
?>
<div class="std_block">
<?php
if (method_exists($object_lo, 'loadObjectReport'))
	echo $object_lo->loadObjectReport(true);
?>
</div>
