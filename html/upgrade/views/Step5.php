<h2><?php echo Lang::t('_UPGRADING'); ?></h2>

<script type="text/javascript">
	var max_upg_step =<?php echo count($_SESSION['to_upgrade_arr']); ?>;
	var cur_upg_step =0;
	var pb;

	YAHOO.util.Event.onDOMReady(function() {
		disableBtnNext(true);
		pb =new YAHOO.widget.ProgressBar({value:0, minValue:0, maxValue:max_upg_step, height:18, width:520}).render('prog_bar');
		goNextStep();
	});

	function goNextStep() {
		var callback_db = {
			success: function(o) {
				var arr =YAHOO.lang.JSON.parse(o.responseText);
				if (arr['res'] == 'ok') {
					if (cur_upg_step < max_upg_step) {
						goNextStep();
					}
					else {
						disableBtnNext(false);
					}
				} else {
					YAHOO.util.Dom.get('error').style.display ='block';
					YAHOO.util.Dom.get('error_text').innerHTML = arr['msg'];

				}



			}
		};

		cur_upg_step++;
		pb.set('value',cur_upg_step);
		disableBtnNext(true);

		var sUrl ='upg_data.php?cur_step=5&upg_step='+cur_upg_step;
		YAHOO.util.Connect.asyncRequest('GET', sUrl, callback_db);
	}
</script>


<div style="text-align: center; margin-top: 150px;">
<div id="prog_bar"></div>
</div>

<div id="error" style="display: none;">
	<p>Upgrade database error</p>
	<div id="error_text">dummy error</div>
</div>
