<html>
<head>
<title>
Connection to server
</title>
<script type="text/javascript">
<!--
function serverCommunication() {
	try {
	if( window.dialogArguments ) {
		switch( window.dialogArguments.func ) {
			case "GetValue":
				window.returnValue = window.dialogArguments.sapi.commonLMSGetValue( window.dialogArguments.param );
			break;
			case "SetValue":
				window.returnValue = window.dialogArguments.sapi.commonLMSSetValue( 	window.dialogArguments.param,
													window.dialogArguments.value
												);
			break;
			case "Initialize":
				window.returnValue = window.dialogArguments.sapi.commonLMSInitialize();
			break;
			case "Commit":
				window.returnValue = window.dialogArguments.sapi.commonLMSCommit();
			break;
			case "Finish":
				window.returnValue = window.dialogArguments.sapi.commonLMSFinish();
			break;
		}
	}
	} catch ( ex ) {
		alert( ex );
	}
	
	window.close();
}

window.onload = function() {
	window.setTimeout( serverCommunication, 50 );
}

//-->
</script>
</head>
<body bgcolor="#888" style="background-color:gray;">
	<div style="text-align:center;">
		Please wait.....
	</div>
</body>
</html>
