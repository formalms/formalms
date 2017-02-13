<?php

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

/**
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @version $Id:$
 *
 */

define("LMS", true);
define("IN_FORMA", true);
//define("IS_AJAX", true);
define("_deeppath_", '../../../');
require(dirname(__FILE__).'/'._deeppath_.'base.php');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_DATETIME);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 Strict//EN"    
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
	
	<!-- <link href="<?php echo getPathTemplate(); ?>style/style.css" rel="stylesheet" type="text/css" /> -->
	<link href="<?php echo getPathTemplate(); ?>style/lms-scormplayer.css" rel="stylesheet" type="text/css" />
	<?php if(!isset($playertemplate) || trim($playertemplate) == '') $playertemplate = 'default'; ?>
	<link href="<?php echo getPathTemplate().'player_scorm/'.$playertemplate; ?>/def_style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		function msgPrereqNotSatisfied( text ) {
			var elem = document.getElementById('prerequisites');
			elem.appendChild(document.createTextNode(text))
			elem.style.visibility = 'visible';
		}
		// inform the player 
		window.onload = function() {
			parent.scormPlayer.blankPageLoaded();
		}
	</script>
	</head>
	<body>
		<div id="bodynav">
			<div id="prerequisites" style="visibility: hidden" >
				<b><?php echo Lang::t('_ORGLOCKEDTITLE', 'organization') ?></b>
			</div>
			<br />
			<div id="prevblocklink">
				<a id="prevsco" href="#" onClick="parent.playprevclick(); return false;">
				<!--	<img src="<?php echo getPathImage(); ?>scorm/bt_sx.png" alt="prev" /> -->
					<span id="prevlink">
					</span>
				</a>
			</div>
			<div id="nextblocklink">
				<a id="nextsco" href="#" onClick="parent.playnextclick(); return false;">
					<span id="nextlink">
					</span>
				<!--	<img src="<?php echo getPathImage(); ?>scorm/bt_dx.png" alt="next" /> -->
				</a>
			</div>
		</div>
		<script type="text/javascript">
			if(parent.prevExist()) {
				var prev = document.getElementById('prevlink');
				prev.innerHTML = parent.scormPlayer.getPrevScoName();
			} else {
				var prev = document.getElementById('prevblocklink');
				prev.style.visibility = 'hidden';
			}
			if(parent.nextExist()) {
				var next = document.getElementById('nextlink');
				next.innerHTML = parent.scormPlayer.getNextScoName();
			} else {
				var next = document.getElementById('nextblocklink');
				next.style.visibility = 'hidden';
			}
		</script>
	</body>
</html>
<?php

// close database connection

sql_close($GLOBALS['dbConn']);

ob_end_flush();

?>