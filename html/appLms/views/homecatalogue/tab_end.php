		<?php
			//Left category
			if ($_GET['r'] !== 'homecatalogue/coursepathCourse') {
				echo '</div>'
				. '</div>'
				. '<div class="yui-b" id="left_categories">';

				$category = $model->getMinorCategory($std_link, true);
				if(!empty($category)) {

					echo '<ul class="flat-categories">';
					foreach ($category as $id_cat => $data) {
						echo '<li>'
						. '<div><a href="' . $std_link . '&amp;id_cat=' . $id_cat . '">' . $data['name'] . '</a></div>'
						. '<ul style="font-size:11px;font-color:#000000;">';

						if (isset($data['son']))
							foreach ($data['son'] as $id_cat_s => $name_s)
								echo '<li><a href="' . $std_link . '&amp;id_cat=' . $id_cat_s . '">' . $name_s . '</a></li>';

						echo '</ul>'
						. '</li>';
					}
					echo '</ul>'
						.'<div class="nofloat"></div>'
						. '</div>';
				} else {
					echo '</div><br />';
				}

				/*. '<ul class="flat-categories">'
				. '<li><a href="' . $std_link . '">' . Lang::t('_ALL_CATEGORIES', 'catalog') . '</a></li>';

				$category = $model->getMajorCategory($std_link);

				foreach ($category as $id_cat => $name)
					echo '<li><a href="' . $std_link . '&amp;id_cat=' . $id_cat . '">' . $name . '</a></li>';

				echo '</ul>'
				. '</div>'
				. '</div>';*/
			}
		?>
	</div>
	<div class="nofloat">&nbsp;</div>
	<?php echo '<div class="home_cat_back"><a href="index.php" class="home_cat_link">'.Lang::t('_BACK', 'standard').'</a></div>'; ?>
</div>