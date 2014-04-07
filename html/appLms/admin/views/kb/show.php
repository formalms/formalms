<script type="text/javascript">
	YAHOO.namespace("KbManagement");
	var lb = new LightBox();

	var KbManagement = {
		selected_node: <?php echo (int) $selected_node; ?>,
		filterText :"",
		type_filter :"0",
		categorized_filter :"all",

		requestBuilder: function (oState, oSelf) {
			var sort, dir, startIndex, results;

			oState = oState || {pagination: null, sortedBy: null};

			startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
			results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
			sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
			dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";

			return  "&results="	+ results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir+
				"&folder_id="+KbManagement.selected_node+
				"&type_filter="+KbManagement.type_filter+
				"&categorized_filter="+KbManagement.categorized_filter+
				"&filter_text="+KbManagement.filterText;
		},

		addFolderCallback: function(o) {
			if (o.node) {
				var parent = TreeView_kbtree._getNodeById(o.id_parent);
				TreeView_kbtree.appendNode(parent, o.node, false);
			}
			this.destroy();
		}
	}

	function frm_edit(elLiner, oRecord, oColumn, oData) {
		var img =(oRecord.getData("is_categorized") == 1 ? 'subs_categorize' : 'fd_notice');
		var msg =(oRecord.getData("is_categorized") == 1 ? '<?php echo Lang::t('_MOD', 'kb'); ?>' : '<?php echo Lang::t('_CATEGORIZE', 'kb'); ?>');
		var r_name = oRecord.getData("r_name");
		elLiner.innerHTML = '<a id="frm_edit_'+oRecord.getData("res_id")+'" class="ico-sprite '+img+'" '
			+' href="index.php?r=alms/kb/edit&amp;id='+oRecord.getData("res_id")+'" title="'+msg+': '+r_name+'">'
			+'<span>'+msg+'</span></a>'	;
	}

	function fv_switch(elLiner, oRecord, oColumn, oData) {
		var title = oData>0 ? '<?php echo addslashes(Lang::t('_SET_AS_NORMAL', 'kb')); ?>' : '<?php echo addslashes(Lang::t('_SET_VISIBLE_TO_EVERYONE', 'kb')); ?>';
		var r_name = oRecord.getData("r_name");
		elLiner.innerHTML = '<a id="fv_switch_'+oRecord.getData("res_id")+'" class="ico-sprite subs_'+(oData>0 ? 'actv' : 'noac')+'" '
			+' href="ajax.adm_server.php?r=alms/kb/fvSwitch&id='+oRecord.getData("res_id")+'&is_active='+(oData)+'" '
			+'onclick="javascript:svSwitch(this); return false;" title="'+title+': '+r_name+'">'
			+'<span>'+title+'</span></a>'	;
	}

	function frm_play(elLiner, oRecord, oColumn, oData) {
		var msg ='<?php echo Lang::t('_PLAY', 'kb'); ?>';
		var lms_path ='<?php echo Get::rel_path('lms'); ?>/';
		var extra ='';
		if (oRecord.getData("r_type") == 'scoitem' || oRecord.getData("r_type") == 'scorm') {
			extra =' rel="lightbox"';
		}
		var r_name = oRecord.getData("r_name");
		elLiner.innerHTML = '<a'+extra+' id="frm_play_'+oRecord.getData("res_id")+'" class="ico-sprite subs_play" '
			+' href="'+lms_path+'index.php?r=kb/play&amp;from_adm=1&amp;id='+oRecord.getData("res_id")+'" title="'+msg+': '+r_name+'">'
			+'<span>'+msg+'</span></a>'	;
	}

	function applySearchFilter() {
		KbManagement.filterText = YAHOO.util.Dom.get("filter_text").value;
		DataTable_kb_table.refresh();
	}

	YAHOO.util.Event.onDOMReady(function(e) {

		YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			YAHOO.util.Dom.get("filter_text").value = "";
			YAHOO.util.Dom.get("res_type_dd").value = "0";
			KbManagement.type_filter ='0';
			YAHOO.util.Dom.get("categorized_filter").value = "all";
			KbManagement.categorized_filter ='all';
			applySearchFilter();
		});

		YAHOO.util.Event.addListener("quick_search", "submit", function(e) {
			YAHOO.util.Event.preventDefault(e);
			applySearchFilter();
		});

		YAHOO.util.Event.addListener("res_type_dd", "change", function(e) {
			KbManagement.type_filter =YAHOO.util.Dom.get("res_type_dd").value;
			DataTable_kb_table.refresh();
		});

		YAHOO.util.Event.addListener("categorized_filter", "change", function(e) {
			KbManagement.categorized_filter =YAHOO.util.Dom.get("categorized_filter").value;
			DataTable_kb_table.refresh();
		});

	});

	function svSwitch(a_elem) {
		sUrl = a_elem.href;
		var callback = {
			success: function(o) { DataTable_kb_table.refresh(); }
		};
		YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
	};

</script>
<?php
echo getTitleArea(array(Lang::t('_CONTENT_LIBRARY', 'kb')));
?>
<div class="std_block">
	<?php echo $result_message; ?>
	<div class="quick_search_form">
		<div>
			<div class="simple_search_box" id="kb_simple_filter_options" style="display: block;">
				<?php
				echo Form::openForm('quick_search', 'javascript:;');
				echo Form::getInputDropdown('dropdown', 'res_type_dd', 'res_type_dd', $res_type_dd_arr, false, '') . "&nbsp;\n";
				echo Form::getInputDropdown('dropdown', 'categorized_filter', 'categorized_filter', $categorized_filter_arr, false, '') . "&nbsp;\n";
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '');
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
				echo Form::closeForm();
				?>
			</div>
		</div>
	</div>
	<div class="panel_left_small">
		<span class="title"><?php echo(Lang::t('_ALL_CATEGORIES', 'kb')); ?></span>
		<?php
		/**
		 * Tree
		 */
		$languages = array(
			'_ROOT' => Get::sett('title_kb_tree', Lang::t('_CATEGORY', 'kb')),
			'_YES' => Lang::t('_CONFIRM', 'organization_chart'),
			'_NO' => Lang::t('_UNDO', 'organization_chart'),
			'_LOADING' => Lang::t('_LOADING', 'standard'),
			'_NEW_FOLDER_NAME' => Lang::t('_ORGCHART_ADDNODE', 'organization_chart'),
			'_AREYOUSURE' => Lang::t('_AREYOUSURE', 'organization_chart'),
			'_NAME' => Lang::t('_NAME', 'standard'),
			'_MOD' => Lang::t('_MOD', 'standard'),
			'_DEL' => Lang::t('_DEL', 'standard')
		);

		$params = array(
			'id' => 'kbtree',
			'ajaxUrl' => 'ajax.adm_server.php?r=alms/kb/gettreedata',
			'treeClass' => 'KbFolderTree',
			'treeFile' => Get::rel_path('lms') . '/admin/views/kb/kbfoldertree.js',
			'languages' => $languages,
			'initialSelectedNode' => (int) $selected_node,
			'rootActions' => '',
			'show' => 'tree',
			'dragDrop' => true
		);
		if ($permissions['add']) {
			$params['rel_action'] =  '<a class="ico-wt-sprite subs_add" id="add_kb_folder" href="ajax.adm_server.php?r=alms/kb/addfolder_dialog&id=' . (int) $selected_node . '" '
				. ' title="' . Lang::t('_ORGCHART_ADDNODE', 'organization_chart') . '">'
				. '<span>' . Lang::t('_ORGCHART_ADDNODE', 'organization_chart') . '</span>'
				. '</a>';

			/**
			 * Add folder dialog
			 */
			$this->widget('dialog', array(
				'id' => 'add_folder_dialog',
				'dynamicContent' => true,
				'ajaxUrl' => 'function() { return YAHOO.util.Dom.get("add_kb_folder").href; }',
				'dynamicAjaxUrl' => true,
				'callback' => 'KbManagement.addFolderCallback',
				'callEvents' => array(
					array('caller' => 'add_kb_folder', 'event' => 'click')
				)
			));
		}

		$this->widget('tree', $params);
	?>
	</div>
	<div class="panel_right_big">
	<?php
		$columns = array(
			array('key' => 'r_name', 'label' => Lang::t('_NAME', 'kb'), 'sortable' => true),
			array('key' => 'r_type', 'label' => Lang::t('_TYPE', 'kb'), 'sortable' => true, 'className' => 'img-cell'),
			array('key' => 'r_env', 'label' => Lang::t('_ENVIRONMENT', 'kb'), 'sortable' => true),
			array('key' => 'r_env_parent', 'label' => Lang::t('_CONTAINED_IN', 'kb'), 'sortable' => false),
			array('key' => 'r_lang', 'label' => Lang::t('_LANGUAGE', 'kb'), 'sortable' => true),
			array('key' => 'tags', 'label' => Lang::t('_TAGS', 'kb'), 'sortable' => false)
		);

		if ($permissions['mod']) {
			$_title_categorize = Lang::t('_MOD', 'kb');
			$_title_users = Lang::t('_SET_VISIBLE_TO_EVERYONE', 'kb');
			$_sprite_categorize = Get::sprite('subs_categorize', $_title_categorize, $_title_categorize);
			$_sprite_users = Get::sprite('subs_users', $_title_users, $_title_users);
			$columns[] = array('key' => 'edit', 'label' => $_sprite_categorize, 'formatter' => 'frm_edit', 'className' => 'img-cell');
			$columns[] = array('key' => 'force_visible', 'label' => $_sprite_users, 'formatter' => 'fv_switch', 'className' => 'img-cell');
		}

		$_title_play = Lang::t('_PLAY', 'kb');
		$_sprite_play = Get::sprite('subs_play', $_title_play, $_title_play);
		$columns[] = array('key' => 'play', 'label' => $_sprite_play, 'formatter' => 'frm_play', 'className' => 'img-cell');

		$this->widget('table', array(
			'id' => 'kb_table',
			'ajaxUrl' => 'ajax.adm_server.php?r=alms/kb/getlist',
			'rowsPerPage' => Get::sett('visuItem', 25),
			'startIndex' => 0,
			'results' => Get::sett('visuItem', 25),
			'sort' => 'r_name',
			'dir' => 'asc',
			'generateRequest' => 'KbManagement.requestBuilder',
			'events' => array(
				'postRenderEvent' => 'function () { lb.init(); }',
			),
			'columns' => $columns,
			'fields' => array(
				'res_id', 'r_name', 'r_type', 'r_env', 'r_env_parent', 'r_lang', 'tags',
				'edit', 'force_visible', 'is_mobile', 'is_categorized'
			),
		));
	?>
	</div>
	<div class="nofloat"></div>
</div>