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

	<div id="" class="">
		<ul class="nav nav-tabs">
	
		<?php

			foreach($menu as $id => $menu_item)  {

				echo '<li'.($id == $active_tab?' class="active"':'').'>'
					.'<a href="#tab_g_'.$id.'" data-toggle="tab">'
					.'<em>'.Lang::t($menu_item->name, 'menu').'</em>'
					.'</a>'
					.'</li>';
            }

		?>

		</ul>
		<div class="tab-content">
			<?php
				foreach($menu as $id => $menu_item)  {

					// print the tab content
					echo '<div class="tab-pane'.($id == $active_tab?' active':'').'" id="tab_g_'.$id.'">'
						.Form::openElementSpace();

					$model->printPageWithElement($menu_item->idMenu, $idst);

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