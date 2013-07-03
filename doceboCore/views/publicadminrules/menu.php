<?php
	$array_title = array(	'index.php?r=adm/publicadminrules/show' => Lang::t('_PUBLIC_ADMIN_RULES', 'menu'),
							Lang::t('_ADMIN_MENU', 'adminrules').' - '.$model->getGroupName($idst));

	echo	getTitleArea($array_title);
?>

<div class="std_block">
	<?php
		echo	$save_res
				.Form::openForm('admin_menu_form', 'index.php?r=adm/publicadminrules/menu&idst='.$idst)
				.Form::getHidden('active_tab', 'active_tab', $active_tab)
				.$model->printPage($idst)
				.Form::openButtonSpace()
				.Form::getButton('save', 'save', Lang::t('_SAVE', 'adminrules'))
				.Form::getButton('back', 'back', Lang::t('_BACK', 'adminrules'))
				.Form::closeButtonSpace()
				.Form::closeForm();
	?>

</div>

<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function(){
	var tab_view = new YAHOO.widget.TabView('global_conf', {});
	//tab_view.tabsChange.subscribe('activeTabChange', function(data){alert('vediamo se vieni chiamato');/*YAHOO.util.Dom.get('active_tab').value = new_value;*/});
});
</script>