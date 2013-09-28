<div>
	<?php
	$lmstab = $this->widget('lms_tab', array(
		'active' => 'games',
		'close' => false
	));
	?>
	<div class="nested_tab" id="tab_content">
		<div id="global_conf" class="yui-navset yui-navset-top">
			<ul class="yui-nav">
				<li class="first <?php echo ($active_tab == 'unread' ? 'selected' : ''); ?>">
					<a href="index.php?r=games/show">
						<em><?php echo Lang::t('_OPEN_COMPETITION', 'games') ?></em>
					</a>
				</li>
				<li class="<?php echo ($active_tab == 'history' ? 'selected' : ''); ?>">
					<a href="index.php?r=games/showhistory">
						<em><?php echo Lang::t('_HISTORY', 'games') ?></em>
					</a>
				</li>
			</ul>
			<div class="yui-content">
				<div class="games_chart">
					<h3><?php echo Lang::t('_OVERALL_RESULTS', 'standard'); ?></h3>
					<div id="standings_chart">Unable to load Flash content. Required Flash Player 9.0.45 or higher. You can download the latest version of Flash Player from the <a href="http://www.adobe.com/go/getflashplayer">Adobe Flash Player Download Center</a>.</div>
					<script type="text/javascript">
						var dataSource = new YAHOO.util.DataSource( <?php echo $chart_data; ?> );
						dataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
						dataSource.responseSchema = {fields: [ "x_axis", "y_axis" ]};

						var xAxis = new YAHOO.widget.NumericAxis();
						xAxis.position = "bottom";
						xAxis.title = "<?php echo Lang::t('_SCORE', 'standard'); ?>";
						xAxis.alwaysShowZero = true;

						var yAxis = new YAHOO.widget.NumericAxis();
						yAxis.position = "left";
						yAxis.title = "<?php echo Lang::t('_USER_OCCURENCY', 'standard'); ?>";
						yAxis.alwaysShowZero = true;

						var myChart = new YAHOO.widget.ColumnChart( "standings_chart", dataSource, {
							xField: "x_axis",
							yField: "y_axis",
							xAxis: xAxis,
							yAxis: yAxis,
							wmode: "opaque",
							style: {
								yAxis: {labelDistance:0,titleRotation:-90}
							}
						});
					</script>
				</div>
				<h2><?php echo $game['title']; ?></h2>
				<p><?php echo Lang::t('_START_DATE', 'standard').': '.Format::date($game['start_date']).' - '.Format::date($game['end_date']); ?></p>
				<br />
				<h3><?php echo Lang::t('_YOUR_RESULT', 'standard'); ?></h3>
				<p>
					<?php echo Lang::t('_LAST_PLAY', 'standard').': '.$track['dateAttempt']; ?><br/>
					<?php echo Lang::t('_CURRENT_SCORE', 'standard').': '.$track['current_score']; ?><br/>
					<?php echo Lang::t('_MAX_SCORE', 'standard').': '.$track['max_score']; ?><br/>
					<?php echo Lang::t('_NUM_ATTEMPTS', 'standard').': '.$track['num_attempts']; ?><br/>
				</p>
				<div class="nofloat"></div>
				<br/>
				<h3><?php echo Lang::t('_FIRST_PLACES', 'standard'); ?></h3>
				<?php
				$tb = new Table(30);
				$tb->addHead(array(
					Lang::t('_POSITION', 'games'),
					Lang::t('_USERNAME', 'standard'),
					Lang::t('_FULLNAME', 'standard'), 
					Lang::t('_CURRENT_SCORE', 'standard'),
					Lang::t('_MAX_SCORE', 'standard'),
				), array('image', '','','image','image'));
				$i = 1;
				foreach($standings as $row) {

					$tb->addBody(array(
						$i++,
						Docebo::aclm()->relativeID($row['userid']),
						$row['lastname'].' '.$row['firstname'],
						$row['current_score'],
						$row['max_score']
					));
				}
				echo $tb->getTable();
				?>
				<div class="nofloat"></div>
			</div>
		</div>
	</div>
	<?php
	// close the tab structure
	$lmstab->endWidget();
	?>
</div>