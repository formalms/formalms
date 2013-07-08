<?php
	$array_title = array(	'index.php?r=adm/adminrules/show' => Lang::t('_ADMIN_RULES', 'adminrules'),
							Lang::t('_ADMIN_MENU', 'adminrules').' - '.$model->getGroupName($idst));

	echo	getTitleArea($array_title);
?>

<div class="std_block">
	<?php
		echo $save_res
				.Form::openForm('admin_menu_form', 'index.php?r=adm/adminrules/menu&idst='.$idst)
				.Form::getHidden('active_tab', 'active_tab', $active_tab);
	?>

	<div id="global_conf" class="yui-navset">
		<ul class="yui-nav">
	
		<?php
			foreach($platform_list as $id => $translate)
				echo '<li'.($id == $active_tab?' class="selected"':'').'>'
					.'<a href="#tab_g_'.$id.'">'
					.'<em>'.$translate.'</em>'
					.'</a>'
					.'</li>';

			reset($platform_list);
		?>

		</ul>
		<div class="yui-content">
			<?php
				while(list($id, $translate) = each($platform_list))
				{
					// print the tab content
					echo '<div id="tab_g_'.$id.'">'
						.Form::openElementSpace();

					$model->printPageWithElement($id, $idst);

					echo	Form::closeElementSpace()
							.'</div>';
				}
			?>
		</div>
		<div class="nofloat">&nbsp;</div>
	</div>

	<?php
		echo	Form::openButtonSpace()
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