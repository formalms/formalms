
<script type="text/javascript">

	var yl_reset_timeout;
	if (!yl_debug) {
		var yl_debug =false;
	}

	// Put a LogReader on your page
	yuiLogReader = new YAHOO.widget.LogReader("log_reader", {
		verboseOutput:false,
		bottom:'2px',
		left:'2px',
		width:'80%',
		height:'80px',
		footerEnabled: false
	});
	yuiLogReader.collapse();
	yuiLogReader.hide();

	function yuiLogAutoReset() {
		yuiLogReader.show();
		yuiLogReader.expand();		
		clearTimeout(yl_reset_timeout);
		yl_reset_timeout =setTimeout('yuiLogReader.collapse(); yuiLogReader.hide(); yuiLogReader.clearConsole();', 30000);
	}

	function yuiLogMsg(msg, type) {
		if (!yl_debug) { return false; }
		if (yuiLogReader.isCollapsed) {
			yuiLogAutoReset();
		}
		if (type == '') {
			type = 'info';
		}
		YAHOO.log(msg, type);
	}

</script>