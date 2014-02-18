<?php //defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

error_reporting(E_ALL ^ E_NOTICE); 
// check for remote file inclusion attempt -------------------------------
$list = array('GLOBALS', '_POST', '_GET', '_COOKIE', '_SESSION'); 
while(list(, $elem) = each($list)) {
		
	if(isset($_REQUEST[$elem])) die('Request overwrite attempt detected');
}

require_once(dirname(__FILE__)."/header.php");

YuiLib::load();

if(!isset($_GET['op'])) $_GET['op'] = 'default';
switch($_GET['op']) {
	case "getmess" : {
		ob_clean();
		echo getMsgBuffer($lang, false, true);
		if( Docebo::user()->isLoggedIn() )
			Docebo::user()->SaveInSession();
		exit;
	};break;
	default: {
		
		$script = "
		<script type=\"text/javascript\">
		<!--
		
		function loadXMLDoc(url)
		{
			var callback =
			{
				success: function(req)
				{
					var write = document.getElementById(\"write_here\");
					if(write)
					{
						while( write.childNodes.length > 200 )
							write.removeChild( write.firstChild );
						
						if(req.responseText.length != 0)
						{
							var newline = document.createElement(\"div\");
							newline.innerHTML = req.responseText;
							write.appendChild(newline);
						}
						
						scroll(1, 10000000);
					}
					else
					{
						alert(\"where to write not found\");
						window.clearInterval(id_interval);
					}
				},
				
				failure: {}, 
				
				cache: false
			}
			var transaction = YAHOO.util.Connect.asyncRequest('GET', url, callback, null);
		}
		
		function sd() {
			scroll(1, 10000000);
		}
		
		function endRefresh() {
			window.clearInterval(id_interval);
		}
		
		-->
		</script>
	";
	$out->add($script, "page_head");
	
	$out->add(
		'<div class="intestation_2">'.$lang->def('_CHAT').'</div>'
		.'<div class="chatText" id="write_here">'
		.'<div>'.getMsgBuffer($lang).'</div>'
		.'</div>'
		.'<script type="text/javascript">
			var id_interval 	= window.setInterval("loadXMLDoc(\''.getPopupBaseUrl().'&op=getmess\')", '._REFRESH_RATE.' * 1000);
			sd();
		</script>');
	}
}

// -------------------------------------------------------------------
require_once(dirname(__FILE__)."/footer.php");




?>