<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		var assignDelEvents = function(form_prefix, del_prefix, title_prefix) {
			var elList = YAHOO.util.Selector.query('a[id^='+del_prefix+']');
			YAHOO.util.Event.addListener(elList, "click", function(e) {
				YAHOO.util.Event.preventDefault(e);

				var confirm = function() { this.submit(); };
				var undo = function() { this.destroy(); };

				var setDialogErrorMessage = function(message) {
					var el = YAHOO.util.Dom.get("<?php echo $id; ?>_del_dialog_message");
					if (el) el.innerHTML = message;
				}

				var delDialog = new YAHOO.widget.Dialog("usertable_delDialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					buttons: [
						{text:"<?php echo Lang::t('_CONFIRM', 'standard'); ?>", handler: confirm, isDefault: true},
						{text:"<?php echo Lang::t('_UNDO', 'standard'); ?>", handler: undo}
					]
				});

				delDialog.hideEvent.subscribe(function(e, args) {
					YAHOO.util.Event.stopEvent(args[0]);
					this.destroy();
				}, delDialog);

				delDialog.callback = {
					success: function(oResponse) {
						var x, o = YAHOO.lang.JSON.parse(oResponse.responseText);
						if (o.success) {
							delDialog.destroy();
							YAHOO.util.Dom.get(form_prefix+'tab_advice').submit();
						} else {
							setDialogErrorMessage(o.message ? o.message : "<?php echo Lang::t('_OPERATION_FAILURE'); ?>");
						}
					},
					failure: function() { setDialogErrorMessage("<?php echo Lang::t('_CONNECTION_ERROR', 'standard'); ?>"); },
					scope: delDialog
				};

				var el_name = YAHOO.util.Dom.get(title_prefix+this.id.replace(del_prefix, ''));
				delDialog.setHeader("<?php echo Lang::t('_AREYOUSURE'); ?>");
				delDialog.setBody(
					'<div id="<?php echo $id; ?>_del_dialog_message"></div>'
					+'<form method="POST" id="<?php echo $id; ?>_del_dialog_form" action="'+this.href+'">'
					+'<p><?php echo Lang::t('_DEL', 'standard'); ?>:&nbsp;<b>'+(el_name ? el_name.innerHTML : '')+'</b></p>'
					+'</form>'
				);

				delDialog.render(document.body);
				delDialog.show();
			});
		};

		assignDelEvents('inbox_', '_del_inbox_', '_title_inbox_');
		assignDelEvents('outbox_', '_del_outbox_', '_title_outbox_');
	});
</script>
