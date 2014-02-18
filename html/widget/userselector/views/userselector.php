<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		var tabs = new YAHOO.widget.TabView("<?php echo $id; ?>_tabview");
	});
</script>
<?php if ($use_form_input && !$separate_input)
	echo '<input type="hidden" id="userselector_input_' . $id . '" name="userselector_input[' . $id . ']" value="" />'; ?>
<div id="<?php echo $id; ?>_tabview" class="yui-navset">
	<ul class="yui-nav">
		<?php
		if ($show_user_selector) { ?>
			<li<?php if ($selected_tab == 'user') echo ' class="selected"'; ?>>
			<a href="#<?php echo $id . '_user_tab'; ?>"><em><?php echo Lang::t('_USERS', 'admin_user_management'); ?></em></a>
		</li>
		<?php
		}
		if ($show_group_selector) { ?>
			<li<?php if ($selected_tab == 'group') echo ' class="selected"'; ?>>
			<a href="#<?php echo $id . '_group_tab'; ?>"><em><?php echo Lang::t('_GROUPS', 'admin_user_management'); ?></em></a>
		</li>
		<?php
		}
		if ($show_orgchart_selector) { ?>
			<li<?php if ($selected_tab == 'orgchart') echo ' class="selected"'; ?>>
			<a href="#<?php echo $id . '_orgchart_tab'; ?>"><em><?php echo Lang::t('_ORGCHART', 'admin_user_management'); ?></em></a>
		</li>
		<?php
		}
		if ($show_fncrole_selector) { ?>
			<li<?php if ($selected_tab == 'fncrole')
				echo ' class="selected"'; ?>>
			<a href="#<?php echo $id . '_fncrole_tab'; ?>"><em><?php echo Lang::t('_FUNCTIONAL_ROLE', 'fncroles'); ?></em></a>
		</li>
		<?php
		} ?>
	</ul>
	<div class="yui-content">
		<?php
		if ($show_user_selector) {
			echo '<div id="' . $id . '_user_tab">';
			$this->render('_user_selector', array(
				'id' => $id,
				'use_form_input' => $use_form_input,
				'separate_input' => $separate_input,
				'initial_selection' => $initial_selection['user'],
				'num_users_selected' => count($initial_selection['user']),
				'num_var_fields' => $user_config->num_var_fields,
				'filter_text' => $user_config->filter_text,
				'dynamic_filter' => $user_config->dynamic_filter,
				'use_suspended' => $user_config->use_suspended,
				'show_suspended' => $user_config->show_suspended,
				'fieldlist' => $user_config->fieldlist,
				'fieldlist_js' => $user_config->fieldlist_js,
				'selected' => $user_config->selected,
				'learning_filter' => $this->learning_filter
			));
			echo '</div>';
		}
		if ($show_group_selector) {
			echo '<div id="' . $id . '_group_tab">';
			$this->render('_group_selector', array(
				'id' => $id,
				'filter_text' => "", //TO DO: read from session ...
				'use_form_input' => $use_form_input,
				'separate_input' => $separate_input,
				'initial_selection' => $initial_selection['group'],
				'num_groups_selected' => count($initial_selection['group']),
				'learning_filter' => $this->learning_filter
			));
			echo '</div>';
		}
		if ($show_orgchart_selector) {
			echo '<div id="' . $id . '_orgchart_tab">';
			$this->render('_orgchart_selector', array(
				'id' => $id,
				'use_form_input' => $use_form_input,
				'separate_input' => $separate_input,
				'selected_node' => 0,
				'initial_selection' => $initial_selection['orgchart'],
				'root_node_id' => $orgchart_config->root_node_id,
				'can_select_root' => $orgchart_config->can_select_root,
				'learning_filter' => $this->learning_filter
			));
			echo '</div>';
		}
		if ($show_fncrole_selector) {
			echo '<div id="' . $id . '_fncrole_tab">';
			$this->render('_fncrole_selector', array(
				'id' => $id,
				'filter_text' => "", //TO DO: read from session ...
				'use_form_input' => $use_form_input,
				'separate_input' => $separate_input,
				'initial_selection' => $initial_selection['fncrole'],
				'num_fncroles_selected' => count($initial_selection['fncrole']),
				'learning_filter' => $this->learning_filter
			));
			echo '</div>';
		}
		?>
	</div>
</div>