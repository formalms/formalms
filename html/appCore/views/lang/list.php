<?php Get::title(array(
	'index.php?r=adm/lang/show' => Lang::t('_LANGUAGE', 'admin_lang'),
	Lang::t('_TRANSLATELANG', 'admin_lang')
)); ?>

<div class="std_block">
	<div class="container-back"><a href="index.php?r=adm/lang/show"><span><?php echo Lang::t('_BACK', 'standard'); ?></span></a></div>
	<script type="text/javascript">
		var requestBuilder = function (oState, oSelf) {
			var sort, dir, startIndex, results;
			oState = oState || {pagination: null, sortedBy: null};

			startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
			results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
			sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
			dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";

			return "&results=" + results +
					"&startIndex=" + startIndex +
					"&sort=" + sort +
					"&dir=" + dir+
					"&la_module=" + YAHOO.util.Dom.get('la_module').value +
					"&la_text=" + YAHOO.util.Dom.get('la_text').value +
					"&lang_code=" + YAHOO.util.Dom.get('lang_code').value +
					"&lang_code_diff=" + YAHOO.util.Dom.get('lang_code_diff').value +
					"&only_empty=" + YAHOO.util.Dom.get('only_empty').checked +
					"&plugin_id=" + YAHOO.util.Dom.get('plugin_id').value;
		}
		var saveTranslation = function(callback, newData) {
			var new_value = newData;
			var old_value =  this.value;
			var datatable = this.getDataTable();
			var id_text = this.getRecord().getData("id");
			var id_translation = this.getRecord().getData("id_translation");

			var myCallback = {
				success: function(o) {
					var r = YAHOO.lang.JSON.parse(o.responseText);
					if (r.success) {
						callback(true, stripSlashes(r.new_value));
					} else {
						callback(true, stripSlashes(r.old_value));
					}
				}, failure:	{}
			}

			var post =	"id_text=" + encodeURIComponent(id_text)
						+"&lang_code=" + encodeURIComponent(YAHOO.util.Dom.get('lang_code').value)
						+"&new_value=" + encodeURIComponent(new_value)
						+"&old_value=" + encodeURIComponent(old_value);
            console.log(post)
			var url = "ajax.adm_server.php?r=adm/lang/saveData&";

			YAHOO.util.Connect.asyncRequest("POST", url, myCallback, post);
		}
		var saveComparisonTranslation = function(callback, newData) {
			var new_value = newData;
			var old_value =  this.value;
			var datatable = this.getDataTable();
			var id_text = this.getRecord().getData("id");
			var id_translation = this.getRecord().getData("id_translation");

			var myCallback = {
				success: function(o) {
					var r = YAHOO.lang.JSON.parse(o.responseText);
					if (r.success) {
						callback(true, stripSlashes(r.new_value));
					} else {
						callback(true, stripSlashes(r.old_value));
					}
				}, failure:	{}
			}

			var post =	"id_text=" + encodeURIComponent(id_text)
						+"&lang_code=" + encodeURIComponent(YAHOO.util.Dom.get('lang_code_diff').value)
						+"&new_value=" + encodeURIComponent(new_value)
						+"&old_value=" + encodeURIComponent(old_value);

			var url = "ajax.adm_server.php?r=adm/lang/saveData&";
			
			YAHOO.util.Connect.asyncRequest("POST", url, myCallback, post);
		}
		var TranslationFormatter = function(elLiner, oRecord, oColumn, oData) {
			var searched = YAHOO.util.Dom.get('la_text').value;
			if(searched) {
				var regexp = new RegExp(searched, 'gi');
				var string_to_replace = regexp.exec(oData)
				elLiner.innerHTML = oData.replace(regexp, '<span class="highlight">'+string_to_replace+'</span>');

			} else elLiner.innerHTML = oData;
		}
		YAHOO.util.Event.addListener("lang_filters", "submit", function(e) {
			YAHOO.util.Event.preventDefault(e);
			DataTable_lang_table.refresh();
		});
		YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			YAHOO.util.Dom.get('la_module').value = '0';
			YAHOO.util.Dom.get('la_text').value = '';
			DataTable_lang_table.refresh();
		});
	</script>
	<div class="quick_search_form">
		<?php
		echo Form::openForm('lang_filters', 'index.php?r=adm/lang/list', false, false, '', '', 'row')
			.'<div class="module-filter col-xs-12 col-sm-2">'
			.'<label for="la_module">'.Lang::t('_LANG_MODULE', 'admin_lang').'</label> : '
			.Form::getInputDropdown( "search_d", "la_module", "la_module", $module_list, '', ' onchange=" DataTable_lang_table.refresh(); "' )
			.'</div>'
			.'<div class="lang-filter col-xs-12 col-sm-2">'
			.'<label for="lang_code">'.Lang::t('_LANGUAGE', 'admin_lang').'</label>: '
			.Form::getInputDropdown( "search_d", "lang_code", "lang_code", $language_list, array_search( $lang_code, $language_list ), ' onchange=" DataTable_lang_table.refresh(); "' )
			.'</div>'
			.'<div class="lang-confr-filter col-xs-12 col-sm-3">'
			.'<label for="lang_code_diff">'.Lang::t('_LANG_COMPARE', 'admin_lang').'</label>: '
			.Form::getInputDropdown( "search_d", "lang_code_diff", "lang_code_diff", $language_list_diff, '', ' onchange=" DataTable_lang_table.refresh(); "' )
			.Form::getInputCheckbox('only_empty', 'only_empty', '1', false, '')
			.' <label class="label_normal" for="waiting">'.Lang::t('_ONLY_EMPTY', 'admin_lang').'</label>'
			.'</div>'
			.'<div class="plugin_id-filter col-xs-12 col-sm-2">'
			.'<label for="plugin_id">'.Lang::t('_PLUGIN', 'admin_lang').'</label> : '
			.Form::getInputDropdown( "search_d", "plugin_id", "plugin_id", $plugins_ids, '', ' onchange=" DataTable_lang_table.refresh(); "' )
			.'</div>'
			.'<div class="search col-xs-12 col-sm-3">'
			.'<label for="la_text">'.Lang::t('_SEARCH', 'admin_lang').'</label> : '
			.'<div class="input-group">'
			.Form::getSearchInputTextfield( "", "la_text", "la_text", '', '', 255, '' )
			.'<div class="input-group-btn"><button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-search"></span></button></div>'
			.'</div>'
			.'</div>'
			.Form::closeForm();
		?>
	</div>
	<?php
	$this->widget('table', array(
		'id'			=> 'lang_table',
		'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/lang/get&',
		'rowsPerPage'	=> 200,
		'row_per_page_select' => '[50,100,250,500,1000]',
		'startIndex'	=> 0,
		'results'		=> Get::sett('visuItem', 250),
		'sort'			=> 'text_module',
		'dir'			=> 'asc',
		'generateRequest' => 'requestBuilder',
		'columns'		=> array(
			//array('key' => 'id',				'label' => 'id_text', 'className' => 'img-cell'),
			//array('key' => 'id_translation',	'label' => 'id_translation', 'className' => 'img-cell'),
			array('key' => 'text_module', 		'label' => Lang::t('_LANG_MODULE', 'admin_lang'), 'className' => 'min-cell', 'sortable' => true),
			array('key' => 'text_key',			'label' => Lang::t('_LANG_KEY', 'admin_lang'), 'formatter' => 'TranslationFormatter', 'className' => 'min-cell', 'sortable' => true),
			array('key' => 'plugin_name',		'label' => Lang::t('_PLUGIN_NAME', 'admin_lang'), 'className' => 'min-cell', 'sortable' => false),
			array('key' => 'translation_text',	'label' => Lang::t('_LANG_TRANSLATION', 'admin_lang'), 'formatter' => 'TranslationFormatter','editor' => 'new YAHOO.widget.TextareaCellEditor({asyncSubmitter: saveTranslation})', 'sortable' => true ),
			array('key' => 'translation_text_diff',	'label' => Lang::t('_LANG_COMPARE', 'admin_lang'), 'editor' => 'new YAHOO.widget.TextareaCellEditor({asyncSubmitter: saveComparisonTranslation})', 'sortable' => true ),
			array('key' => 'save_date',			'label' => Lang::t('_DATE', 'admin_lang'), 'className' => 'min-cell', 'sortable' => true ),
			array('key' => 'delete',			'label' => '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>', 'formatter' => 'stdDelete', 'className' => 'img-cell')
		),
		'fields'		=> array('id','text_module', 'text_key', 'plugin_name', 'translation_text', 'translation_text_diff', 'save_date', 'delete'),
		'stdSelection' => false,
		'delDisplayField' => 'text_key',
		'rel_actions'	=> array(
			'<a id="add_translation_top" href="ajax.adm_server.php?r=adm/lang/addmask" class="ico-wt-sprite subs_add" title="'.Lang::t('_ADD', 'standard').'"><span>'.Lang::t('_ADD', 'standard').'</span></a>',
			'<a id="add_translation_bottom" href="ajax.adm_server.php?r=adm/lang/addmask" class="ico-wt-sprite subs_add" title="'.Lang::t('_ADD', 'standard').'"><span>'.Lang::t('_ADD', 'standard').'</span></a>',
		)
	));
	?>
</div>
<?php
$this->widget('dialog', array(
	'id' => 'translation_add',
	'dynamicContent' => true,
	'ajaxUrl' => 'ajax.adm_server.php?r=adm/lang/translatemask',
	'callback' => 'function() { this.destroy(); DataTable_lang_table.refresh(); }',
	'callEvents' => array(
		array('caller' => 'add_translation_top', 'event' => 'click'),
		array('caller' => 'add_translation_bottom', 'event' => 'click')
	)
));
?>