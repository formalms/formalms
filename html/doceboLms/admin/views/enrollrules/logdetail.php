<?php Get::title(array(
	'index.php?r=alms/enrollrules/show' => Lang::t('_ENROLLRULES', 'enrollrules'),
	'index.php?r=alms/enrollrules/showlog' => Lang::t('_SHOW_LOGS', 'enrollrules'),
	Lang::t('_DETAILS', 'enrollrules')
)); ?>
<div class="std_block">
<?php
$tb = new Table();
$tb->addHead(
	array(
		Lang::t('_USERNAME', 'enrollrules'),
		Lang::t('_LASTNAME', 'enrollrules'),
		Lang::t('_FIRSTNAME', 'enrollrules'),
		Lang::t('_CODE', 'enrollrules'),
		Lang::t('_COURSE_NAME', 'enrollrules')
	),
	array('min-cell', '', '', 'min-cell', '')
);

while(list(,$obj) = each($data)) {

	$row = array(
		Docebo::aclm()->relativeId($obj->userid),
		$obj->lastname,
		$obj->firstname,
		$obj->code,
		$obj->name,
	);
	$tb->addBody($row);
}
echo $tb->getTable();
?>
</div>