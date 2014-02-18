		<?php
			//Left category
			if (!isset($_GET['r']) || $_GET['r'] !== 'catalog/coursepathCourse') {
				echo '</div>'
				. '</div>'
				. '<div class="yui-b" id="left_categories">'
				. '<ul class="flat-categories">'
                //FORMA Modifica 25/07/2012 GN 
                . '<li><a href="' . $std_link . '">' . Lang::t('_ALL_CATEGORIES', 'catalog') . '</a></li>';


                // *** FORMA - start ***
				if (isset($_GET['id_cat'])) $id_subcat_selected = $_GET['id_cat'];
				else $id_subcat_selected = 0;

				if (isset($_GET['m'])) $month_param = "&amp;m=".$_GET['m'];
				else $month_param = "";

				if ($id_subcat_selected != 0) {
					$query = "SELECT idParent FROM %lms_category WHERE idCategory=".$id_subcat_selected;
					$result = sql_query($query);
					list($id_parent) = sql_fetch_row($result);
				}				
				
				
				// ALE:
				// creo la lista dei padri
				$padri = array($id_subcat_selected);
				$_id_cat = $id_subcat_selected;
				while($_id_cat) {
					if ($id_subcat_selected != 0) {
						$query = "SELECT idParent FROM %lms_category WHERE idCategory=".$_id_cat;
						$result = sql_query($query);
						list($id_parent) = sql_fetch_row($result);
						if ($id_parent) {
							$padri[] = $id_parent;
						}
						$_id_cat = $id_parent;
					}
				}
				//print_r($padri);
				// 
				$n=count($padri);
				$category = $this->model->getMajorCategory($std_link);

				foreach ($category as $id_cat => $name) {
					$n--;
					//echo "step $n, cat $id_cat<br>";

					//echo "<li><a href='" . $std_link . "&amp;id_cat=" . $id_cat . "'>".$name."</a></li>";
					$expanded = '';
					if (in_array($id_cat, $padri))
						$expanded = 'expanded ';
					echo '<li class="'.$expanded.(($_GET['id_cat'] == $id_cat) ? 'selected' : '').'"><a id="li_'.$id_cat.'" href="'.$std_link.'&amp;id_cat='.$id_cat.$month_param.'">'.$name."</a></li>";

					$query = "SELECT idCategory, path"
										." FROM %lms_category"
										." WHERE idParent=".$id_cat." ORDER BY path";
					$result = sql_query($query);
			
					if ($id_subcat_selected == 0){
                        $status = "none";
                    }
                    else {
						if ($id_cat == $padri[$n]){
                            $status = "block";
                        }
						else{
                            $status = "none";
                        }
					}

					echo '<ul id="parent_'.$id_cat.'" style="display:'.$status.'";>';
					$m = $n;
					while(list($id_subcat, $path) = sql_fetch_row($result)) {
						
						$subname = end(explode('/', $path));
						$expanded = '';
						if (in_array($id_subcat, $padri))
							$expanded = 'expanded ';
						echo '<li class="'.$expanded.(($_GET['id_cat'] == $id_subcat) ? 'selected' : '').'"><a id="'.$id_subcat.'" href="' . $std_link . '&amp;id_cat=' . $id_subcat . $month_param.'">' .$subname . '</a></li>';
						
						$query = "SELECT COUNT(idCategory)"
									." FROM %lms_category"
									." WHERE idParent=".$id_subcat;
						$result2 = sql_query($query);						
						$row = sql_fetch_row($result2);
						$m = $n;
						if ($row[0]>0) { list($ret, $n) = get_subcategories($id_subcat, $std_link, $n, $padri); echo $ret; }

					}
					echo "</ul>";
					$n=$m+1;

				}
				
				echo '</ul>'
				. '</div>'
				. '</div>';
			}

			function get_subcategories($id_cat, $std_link, $n, $padri) {
				$n--;
				//echo "step $n, cat $id_cat<br>";
				$ret = "";
				
				$query = "SELECT idCategory, path"
									." FROM %lms_category"
									." WHERE idParent=".$id_cat;
				$result = sql_query($query);
		
				if (isset($_GET['m'])) $month_param = "&amp;m=".$_GET['m'];
				else $month_param = "";

				if ($id_cat == $padri[$n]){
                    $status = "block";
                }
				else{
                    $status = "none";
                }
                $m = $n;
				$ret.="<ul id='parent_".$id_cat."' style=\"display:".$status."\";>";
				while(list($id_subcat, $path) = sql_fetch_row($result)) {
					
					$subname = end(explode('/', $path));
					$expanded = '';
					if (in_array($id_subcat, $padri))
						$expanded = 'expanded ';
					$ret.='<li class="'.$expanded.(($_GET['id_cat'] == $id_subcat) ? 'selected' : '').'"><a id="'.$id_subcat.'" href="' . $std_link . '&amp;id_cat=' . $id_subcat . $month_param.'">' .$subname . '</a></li>';

					$query = "SELECT COUNT(idCategory)"
								." FROM %lms_category"
								." WHERE idParent=".$id_subcat;
					$result2 = sql_query($query);						
					$row = sql_fetch_row($result2);
					$m = $n;
					if ($row[0]>0) {list ($_ret, $n) = get_subcategories($id_subcat, $std_link, $n, $padri); $ret .= $_ret;}
				}
				$n=$m+1;
				$ret.="</ul>";
				return array($ret,$n);
				
			}
// *** FORMA - end ***

		?>
	</div>
	<div class="nofloat">&nbsp;</div>
</div>
