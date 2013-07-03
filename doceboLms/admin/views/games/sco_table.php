
<?php
$title_arr=array();
$title_arr['index.php?r=alms/games/show']=Lang::t('_CONTEST', 'games');
//$title_arr['index.php?r=alms/games/categorize&amp;id_game='.$id_game]=$games_data['title'];
$title_arr[]=$games_data['title']; //Lang::t('_CATEGORIZE', 'kb');
echo(getTitleArea($title_arr));

?>
<div class="std_block">

<?php

	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(Get::sett('visu_course'));


	$h_type = array('', '', '', '', '', 'image');
	$h_content = array(
		Lang::t('_NAME', 'organization'),
		Lang::t('_TYPE', 'kb'),
		Lang::t('_ENVIRONMENT', 'kb'),
		Lang::t('_LANGUAGE', 'kb'),
		Lang::t('_TAGS', 'kb'),
		Lang::t('_CATEGORIZE', 'kb'),
	);

	$tb->setColsStyle($h_type);
	$tb->addHead($h_content);


	$qry = "SELECT t1.idscorm_item, t1.title ".
		" FROM ".$GLOBALS['prefix_lms']."_scorm_items as t1 ".
		" WHERE t1.idscorm_organization='".$id_resource."'
		  AND t1.idscorm_resource != 0
			ORDER BY t1.idscorm_item";

	$q =sql_query($qry);
	$i =0;
	$data =array();
	$sco_arr =array();
	while ($row = mysql_fetch_assoc($q)) {

		$sco_id =$row["idscorm_item"];
		$sco_arr[]=$sco_id;

		$data[$i]["idscorm_item"]=$sco_id;
		$data[$i]["title"]=$row['title'];

		$url ='index.php?r=alms/games/categorize&amp;id_game='.$id_game.'
			&amp;idResource='.$id_resource.'&amp;sco_id='.$sco_id;
		$data[$i]["url"] =$url;

		$i++;
	}

	require_once(_lms_.'/lib/lib.kbres.php');
	$kbres =new KbRes();
	$categorized_sco =$kbres->getCategorizedResources($sco_arr, "scoitem", "games", true);
	$categorized_sco_id =(!empty($categorized_sco) ? array_keys($categorized_sco) : array());

	foreach ($data as $row) {

		$line = array();

		$sco_id =$row["idscorm_item"];

		$line[] = $row['title'];

		$categorized =false;
		if (in_array($sco_id, $categorized_sco_id)) {
			$res_id =$categorized_sco[$sco_id]['res_id'];
			$line[]=$categorized_sco[$sco_id]['r_type'];
			$line[]=$categorized_sco[$sco_id]['r_env'];
			$line[]=$categorized_sco[$sco_id]['r_lang'];
			$line[]=(isset($categorized_sco['tags'][$res_id]) ? implode(',', $categorized_sco['tags'][$res_id]) : '');
			$categorized =true;
		}
		else {
			array_push($line, '', '', '', '');
		}

		if ($categorized) {
			$img ='<img src="'.getPathImage().'standard/categorize.png"
				alt="'.Lang::t('_CATEGORIZE', 'kb').'"
				title="'.Lang::t('_CATEGORIZE', 'kb').'" />';
			$line[] ='<a href="'.$row['url'].'">'.$img.'</a>';
		}
		else {
			$line[]='<a class="ico-sprite fd_notice" title="'.Lang::t('_NOT_CATEGORIZED', 'kb').'"
				href="'.$row['url'].'"><span>'.
				Lang::t('_NOT_CATEGORIZED', 'kb').'</span></a>';
		}

		$tb->addBody($line);
	}

	echo $tb->getTable();


	
	echo '<div class="align-right">';
	echo '<a href="#" id="subcategorize_switch" class="ico-wt-sprite subs_del"><span>'.
		Lang::t('_CATEGORIZE_WHOLE_OBJECT', 'kb').'</span></a>';
	echo "</div>\n";

	$body =Form::openForm('add_res', 'index.php?r=alms/games/categorize&amp;id_game='.$id_game)
		.Form::getHidden('subcategorize_switch', 'subcategorize_switch', '0')
		.Form::closeForm();
	$body.=Lang::t('_YOU_WILL_LOSE_PREVIOUS_CATEGORIZATION', 'kb');

	$this->widget('dialog', array(
		'id' => 'subcategorize_switch_dialog',
		'dynamicContent' => false,
		'dynamicAjaxUrl' => false,
		'directSubmit'=>true,
		'header' => Lang::t('_AREYOUSURE', 'kb'),
		'body' => $body,
		'callback' => 'function() { this.destroy(); }',
		'callEvents' => array(
			array('caller' => 'subcategorize_switch', 'event' => 'click')
		)
	));

?>

</div>