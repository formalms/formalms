<div id="<?php echo $id; ?>_table_container">
<?php if($print_table_over) : ?>
<div class="table-container-over">
	<div class="table-actions">
		<?php
			echo ( !is_array($rel_actions) ? $rel_actions : $rel_actions[0]);
			if ($useStdSelectFormatter && $use_paginator) {
				echo '<p class="table_selection">'
					.Lang::t('_SELECT', 'directory').': '
					.'<a class="" id="'.$id.'_select_all_up" href="#"><span>'.Lang::t('_ALL_PAGES', 'directory').'</span></a>'
					.', '
					.'<a class="" id="'.$id.'_unselect_all_up" href="#"><span>'.Lang::t('_NONE', 'directory').'</span></a>'
					.'</p>';
			}
		?>
	</div>
	<?php if($use_paginator){ ?><div id="<?php echo $id; ?>_pag_over"></div><?php }?>
	<div class="nofloat"></div>
</div>
<?php endif; ?>
<!-- Table -->
<div id="<?php echo $id; ?>"></div>
<?php if($print_table_below) : ?>
<div class="table-container-below">
	<div class="table-actions">
		<?php
			echo ( !is_array($rel_actions) ? $rel_actions : $rel_actions[1]);
			if ($useStdSelectFormatter && $use_paginator) {
				echo '<p>'
					.Lang::t('_SELECT', 'directory').': '
					.'<a class="" id="'.$id.'_select_all_down" href="#"><span>'.Lang::t('_ALL_PAGES', 'directory').'</span></a>'
					.', '
					.'<a class="" id="'.$id.'_unselect_all_down" href="#"><span>'.Lang::t('_NONE', 'directory').'</span></a>'
					.'</p>';
			}
		?>
	</div>
	<?php if($use_paginator){ ?><div id="<?php echo $id; ?>_pag_below"></div><?php }?>
	<div class="nofloat"></div>
</div>
<?php endif; ?>
</div>
<script type="text/javascript">
if (!YAHOO.DataTableLangManager) {
	YAHOO.namespace("DataTableLangManager");
	YAHOO.DataTableLangManager = new LanguageManager({
		_DELETE: "<?php echo Lang::t('_DEL', 'standard'); ?>",
		_EDIT: "<?php echo Lang::t('_MOD', 'standard'); ?>",
		_MAKE_A_COPY: "<?php echo Lang::t('_MAKE_A_COPY', 'standard'); ?>"
	});
}

YAHOO.namespace("DataTable_<?php echo $id; ?>");
var DataTable_<?php echo $id; ?>;
<?php if ($useStdSelectFormatter) echo 'var DataTableSelector_'.$id.';'."\n"; ?>

YAHOO.util.Event.onDOMReady(function() {
	var oConfig = {
		id: "<?php echo $id; ?>",

		ajaxUrl: "<?php echo $ajaxUrl; ?>",

		columns: <?php echo $columns; ?>,
		fields: <?php echo $fields; ?>,

		sort: "<?php echo $sort; ?>",
		dir: "<?php echo $dir; ?>",

		usePaginator: <?php echo $use_paginator ? 'true' : 'false' ?>


	};

	<?php
		if ($use_paginator) echo 'oConfig.paginatorParams = {rowsPerPage: '.$rowsPerPage.' '.$paginatorConfig.'};'."\n";
		if ($generateRequest) echo 'oConfig.generateRequest = '.$generateRequest.';'."\n";
		if (isset($scroll_x) && $scroll_x) echo 'oConfig.scrollX = "'.$scroll_x.'";'."\n";
		if (isset($scroll_y) && $scroll_y) echo 'oConfig.scrollY = "'.$scroll_y.'";'."\n";
	?>



var initDataTable = function(oConfig) {

	var L = new LanguageManager();

	var columnDefs = oConfig.columns;

	var oDs = new YAHOO.util.DataSource(oConfig.ajaxUrl);
	oDs.responseType = YAHOO.util.DataSource.TYPE_JSON;
	oDs.connMethodPost = true;
	oDs.responseSchema = {
		resultsList: "records",
		fields: oConfig.fields,
		metaFields: {
			startIndex: "startIndex",
			totalRecords: "totalRecords"
		}
	};


	var configs = {
		initialLoad: false,
		dynamicData: true,
		sortedBy : {key: oConfig.sort, dir: oConfig.dir}
	};

	if (oConfig.scrollX) configs.width = oConfig.scrollX;
	if (oConfig.scrollY) configs.height = oConfig.scrollY;
	if (oConfig.generateRequest) configs.generateRequest = oConfig.generateRequest;
	if (oConfig.usePaginator) configs.paginator = new YAHOO.widget.Paginator(oConfig.paginatorParams);

	var table_type = (oConfig.scrollX || oConfig.scrollY) ? YAHOO.widget.ScrollingDataTable : YAHOO.widget.DataTable;
	var oDt = new table_type(oConfig.id, columnDefs, oDs, configs);

	if (oConfig.usePaginator) {
		oDt.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
			oPayload.totalRecords = oResponse.meta.totalRecords;
			oPayload.pagination.recordOffset = oResponse.meta.startIndex;
			return oPayload;
		};
	}

	//YAHOO.lang.augmentObject(oDt, { refresh: ... });
	oDt.refresh = function() {
		var oDt = this;
		var oState = oDt.getState();
		var request = oDt.get("generateRequest")(oState, oDt);
		var oCallback = {
			success : function(oRequest, oResponse, oPayload) {
				oPayload.totalRecords = oResponse.meta.totalRecords;
				if (oConfig.usePaginator) oPayload.pagination.recordOffset = oResponse.meta.startIndex;
				this.onDataReturnSetRows(oRequest, oResponse, oPayload);
			},
			failure : oDt.onDataReturnSetRows,
			argument : oState,
			scope : oDt
		};
		oDt.showTableMessage(oDt.get("MSG_LOADING"), YAHOO.widget.DataTable.CLASS_LOADING);
		oDt.getDataSource().sendRequest(request, oCallback);
	};


	//YAHOO.lang.augmentObject(oDt, { stdDialogIcons: ... });

	<?php
		if (isset($stdDialogIcons) && is_array($stdDialogIcons) && !empty($stdDialogIcons)) {
			echo 'oDt.stdDialogIcons = [];';
			foreach ($stdDialogIcons as $key => $style) {
				if ($key != "" && $style != "")
					echo 'oDt.stdDialogIcons["'.$key.'"] = "'.$style.'"';
			}
		}
	?>

	<?php
		if (isset($events) && is_array($events)) {
			foreach ($events as $name=>$event) {
				echo 'oDt.subscribe("'.$name.'", '.$event.');'."\n" ;
			}
		}
	?>


	<?php if ($this->editorSaveEvent) : ?>
		oDt.subscribe("cellMouseoverEvent", highlightEditableCell);
		oDt.subscribe("cellMouseoutEvent", oDt.onEventUnhighlightCell);
		oDt.subscribe("cellClickEvent", oDt.onEventShowCellEditor);
		oDt.subscribe("editorSaveEvent", <?php echo $this->editorSaveEvent; ?>);
	<?php endif; ?>

	<?php if ($useStdSelectFormatter) { ?>

		var TableSelector = new ElemSelector("<?php echo $id; ?>_");

		oDt.subscribe("beforeRenderEvent", function() {
			YAHOO.util.Dom.get("<?php echo $id; ?>_head_select").disabled = true;
			var elList = YAHOO.util.Selector.query('input[id^=<?php echo $id; ?>_sel_]');
			YAHOO.util.Event.purgeElement(elList);
		});

		oDt.subscribe("postRenderEvent", function() {
			var h = YAHOO.util.Dom.get("<?php echo $id; ?>_head_select");
			h.disabled = false;
			var elList = YAHOO.util.Selector.query('input[id^=<?php echo $id; ?>_sel_]');
			var allSelected = function(list) {
				var i, all = false;
				for (i=0; i<list.length; i++)
					if (!list[i].checked) {
						all = false;
						break;
					}
				return all;
			}
			h.checked = allSelected(elList);
			YAHOO.util.Event.addListener(elList, "click", function(e) {
				if (this.checked) {
					TableSelector.addsel(this.value);
					h.checked = allSelected(elList);
				} else {
					TableSelector.remsel(this.value);
					h.checked = false;
				}
			});
		});


		YAHOO.util.Event.onContentReady("<?php echo $id; ?>_head_select", function() {
			YAHOO.util.Event.addListener(this, "click", function(e) {
				var i, elList = YAHOO.util.Selector.query('input[id^=<?php echo $id; ?>_sel_]');
				for (i=0; i<elList.length; i++) {
					elList[i].checked = this.checked;
					if (this.checked)
						TableSelector.addsel(elList[i].value);
					else
						TableSelector.remsel(elList[i].value);
				}
			});
		});

		YAHOO.util.Event.addListener(["<?php echo $id; ?>_select_all_up", "<?php echo $id; ?>_select_all_down"], "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			oDt.showTableMessage("<?php echo Lang::t('_LOADING', 'admin_directory'); ?> ...", YAHOO.widget.DataTable.CLASS_LOADING);
			/*var postdata = <?php echo isset($selectAllAdditionalFilter) ? $selectAllAdditionalFilter : '""';?>;*/
			var postdata = "";
			<?php if (isset($selectAllAdditionalFilter)) { ?>
				var add = <?php echo $selectAllAdditionalFilter; ?>;
				if (YAHOO.lang.isFunction(add))
					postdata += add.call(oDt);
				else
					postdata += add+"";
			<?php } ?>
			YAHOO.util.Connect.asyncRequest("POST", "<?php echo $ajaxUrl; ?>&op=selectall", {
				success: function(o) {
					var i, users = YAHOO.lang.JSON.parse(o.responseText);
					//for (i=0; i<users.length; i++) TableSelector.addsel(users[i]);
					TableSelector.addElements(users);

					var elList = YAHOO.util.Selector.query('input[id^=<?php echo $id; ?>_sel_]');
					for (i=0; i<elList.length; i++) {
						elList[i].checked = true;
						TableSelector.addsel(elList[i].value);
					}

					YAHOO.util.Dom.get("<?php echo $id; ?>_head_select").checked = true;

					oDt.hideTableMessage();
				},
				failure: function() {
					oDt.hideTableMessage();
				}
			}, postdata);
		});

		YAHOO.util.Event.addListener(["<?php echo $id; ?>_unselect_all_up", "<?php echo $id; ?>_unselect_all_down"], "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			TableSelector.reset();
			var elList = YAHOO.util.Selector.query('input[id^=<?php echo $id; ?>_sel_]');
			for (i=0; i<elList.length; i++) elList[i].checked = false;
			YAHOO.util.Dom.get("<?php echo $id; ?>_head_select").checked = false;
		});

		<?php if (isset($initialSelection)) echo 'TableSelector.initSelection('.$initialSelection.', true);'."\n"; ?>
		DataTableSelector_<?php echo $id; ?> = TableSelector;

	<?php } ?>

	<?php if ($useStdDeleteFormatter) { ?>

		oDt.subscribe("beforeRenderEvent", function() {
			var elList = YAHOO.util.Selector.query('a[id^=<?php echo $id; ?>_del_]');
			YAHOO.util.Event.purgeElement(elList);
		});

		oDt.subscribe("postRenderEvent", function() {
			var elList = YAHOO.util.Selector.query('a[id^=<?php echo $id; ?>_del_]');
			YAHOO.util.Event.addListener(elList, "click", function(e) {
				var oRecord = oDt.getRecord(this);
				CreateDialog("<?php echo $id; ?>_del_dialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					header: "<?php echo addslashes(Lang::t('_AREYOUSURE')); ?>",
					body: '<div id="<?php echo $id; ?>_del_dialog_message"></div>'
						+'<form method="POST" id="<?php echo $id; ?>_del_dialog_form" action="'+this.href+'">'
						+<?php echo (isset($delDisplayField) ? '"<p>'.addslashes(Lang::t('_DEL', 'standard')).':&nbsp;<b>"+oRecord.getData("'.$delDisplayField.'")+"</b></p>"' : '""');  ?>
						+'</form>',
					callback: function(o) {
						<?php
							if (isset($stdDeleteCallbackEvent) && $stdDeleteCallbackEvent) {
								echo $stdDeleteCallbackEvent.'.call(this, o);'."\n"; //an event to call whit scope = this and arguments = callback arguments
							}
						?>
						this.destroy();
						oDt.refresh();
					}
				}).call(this, e);
			});
		});


	<?php } ?>

	<?php if ($useStdModifyFormatter) { ?>

		oDt.subscribe("beforeRenderEvent", function() {
			var elList = YAHOO.util.Selector.query('a[id^=<?php echo $id; ?>_mod_]');
			YAHOO.util.Event.purgeElement(elList);
		});

    oDt.subscribe("postRenderEvent", function() {
				var elList = YAHOO.util.Selector.query('a[id^=<?php echo $id; ?>_mod_]');
				YAHOO.util.Event.addListener(elList, "click", function(e) {
					CreateDialog("<?php echo $id; ?>_mod_dialog", {
						modal: true,
						close: true,
						visible: false,
						fixedcenter: false,
						constraintoviewport: false,
						draggable: true,
						hideaftersubmit: false,
						isDynamic: true,
						ajaxUrl: this.href,
						callback: function() {
							this.destroy();
							oDt.refresh();
						},
						upload: function() {
							this.destroy();
							oDt.refresh();
						}
						<?php if (isset($stdModifyRenderEvent) && $stdModifyRenderEvent) echo ',renderEvent: '.$stdModifyRenderEvent; ?>
						<?php if (isset($stdModifyDestroyEvent) && $stdModifyDestroyEvent) echo ',destroyEvent: '.$stdModifyDestroyEvent; ?>
					}).call(this, e);
				});
			});
	<?php } ?>

	<?php if ($useStdDialogFormatter) { ?>
		oDt.subscribe("beforeRenderEvent", function() {
			var elList = YAHOO.util.Selector.query('a[id^=<?php echo $id; ?>_frm_]');
			YAHOO.util.Event.purgeElement(elList);
		});

		oDt.subscribe("postRenderEvent", function() {
			var elList = YAHOO.util.Selector.query('a[id^=<?php echo $id; ?>_frm_]');
			YAHOO.util.Event.addListener(elList, "click", function(e) {
				var dialog = CreateDialog("<?php echo $id; ?>_std_dialog", {
					//width: ...
					modal: true,
					close: true,
					visible: false,
					fixedcenter: false,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: true,
					ajaxUrl: this.href,
					callback: function() {
						this.destroy();
					}
				});
				dialog.call(this, e);
			});
		});

	<?php } ?>

		oDt.refresh();
		DataTable_<?php echo $id; ?> = oDt;

	<?php if ($useStdSelectFormatter) { ?>
		DataTable_<?php echo $id; ?>.innerSelector = TableSelector;

		DataTable_<?php echo $id; ?>.selectPage = function() {
			var list = YAHOO.util.Selector.query('input[id^=<?php echo $id; ?>_sel_');
			for (i=0; i<list.length; i++) {
				list[i].checked = true;
				this.innerSelector.addsel(list[i].value);
			}
		};

		DataTable_<?php echo $id; ?>.unselectPage = function() {
			var list = YAHOO.util.Selector.query('input[id^=<?php echo $id; ?>_sel_');
			for (i=0; i<list.length; i++) {
				list[i].checked = false;
				this.innerSelector.remsel(list[i].value);
			}
		};
	<?php } ?>

	<?php if ($useDupFormatter) { ?>

		oDt.subscribe("beforeRenderEvent", function() {
			var elList = YAHOO.util.Selector.query('a[id^=<?php echo $id; ?>_dup_]');
			YAHOO.util.Event.purgeElement(elList);
		});

		oDt.subscribe("postRenderEvent", function() {
			var elList = YAHOO.util.Selector.query('a[id^=<?php echo $id; ?>_dup_]');
			YAHOO.util.Event.addListener(elList, "click", function(e) {
				var oRecord = oDt.getRecord(this);
				CreateDialog("<?php echo $id; ?>_confirm", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					header: "<?php echo Lang::t('_MAKE_A_COPY', 'standard'); ?>",
					body: '<div id="<?php echo $id; ?>_dup_message"></div>'
						+'<form method="POST" id="<?php echo $id; ?>_dup_form" action="'+this.href+'">'
						+<?php echo '"<p>'.Lang::t('_MAKE_A_COPY', 'standard').'</p>"'; ?>

						+'<br/>'
						+'<input class="check" type="checkbox" id="image" name="image" value="1">'
						+'<label class="label_normal" for="image"> <?php echo addslashes(Lang::t('_IMAGES', 'standard')); ?></label>'

						+'<br/>'
						+'<input class="check" type="checkbox" id="certificate" name="certificate" value="1">'
						+'<label class="label_normal" for="certificate"> <?php echo addslashes(Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course')); ?></label>'

						+'<br/>'
						+'<input class="check" onclick="getLoList(\''+this.href.split("=")[2]+'\')" type="checkbox" id="lo" name="lo" value="1">'
						+'<label class="label_normal" for="lo"> <?php echo addslashes(Lang::t('_LEARNING_OBJECTS', 'standard')); ?></label>'

						+'<br/>'
						+'<div id="lo_list"></div>'
						+'<input class="check" type="checkbox" id="advice" name="advice" value="1">'
						+'<label class="label_normal" for="advice"> <?php echo addslashes(Lang::t('_ADVICE', 'advice')); ?></label>'

						+'<input type="hidden" id="confirm" name="confirm" value="1" />'
						+'</form>',
					callback: function() {
						this.destroy();
						oDt.refresh();
					}
				}).call(this, e);
			});
		});

		cascade = function(id) {
			a = YAHOO.util.Dom.get(id);
			var elements = YAHOO.util.Dom.getElementsByClassName(id);
			for (var e in elements) {  
				if (a.checked) {
					elements[e].disabled = false;
					elements[e].checked = true;
				} else {
					elements[e].disabled = true;
					elements[e].checked = false;
				}
			}
		};

		getLoList = function(id) {
			var callback = {
				customevents:{
					onStart: function(eventType, args) {
						YAHOO.util.Dom.get('lo_list').innerHTML = 'loading';
					},
					onComplete: function(eventType, args) {
					},
					onSuccess: function(eventType, args) {
						YAHOO.util.Dom.get('lo_list').innerHTML = args[0].responseText;
					},
					onFailure: function(eventType, args) {
						YAHOO.util.Dom.get('lo_list').innerHTML = '';
					}

				}
			};
			if (YAHOO.util.Dom.get('lo_list').innerHTML == '')
				var cObj = YAHOO.util.Connect.asyncRequest('POST', '../appCore/ajax.adm_server.php?r=alms/course/getLoList&idCourse='+id, callback);
			else
				YAHOO.util.Dom.get('lo_list').innerHTML = '';
		};


	<?php } ?>
};

initDataTable(oConfig);
});
YAHOO.DataTable_<?php echo $id; ?> = DataTable_<?php echo $id; ?>;
</script>
