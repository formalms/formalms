<script type="text/javascript">
	var kb_pagination_items_per_page = <?php echo Get::sett('visuItem', 25); ?>;
	var ajax_url_select_folder = "<?php echo $url_select_folder; ?>";
	var lb = new LightBox();
	var kb_lang ={
		search_result: "<?php echo Lang::t('_SHOW_RESULTS', 'kb'); ?>",
		all_folders: "<?php echo Lang::t('_ALL_CATEGORIES', 'kb'); ?>",
		title_play: "<?php echo Lang::t('_PLAY', 'storage'); ?>",
		_MOD: "<?php echo Lang::t('_MOD', 'kb'); ?>"
	}
</script>
<div class="middlearea_container">
	<?php
	$lmstab = $this->widget('lms_tab', array(
				'active' => 'kb',
				'close' => false
			));
            
	?>

<div class="quick_search_form navbar forma-quick-search-form"> 
        <div class="simple_search_box" id="usermanagement_simple_filter_options" style="display: block;">
            <?php
            echo Form::openForm('quick_search', '');
            echo Form::getInputDropdown('dropdown', 'course_filter', 'course_filter', $course_filter_arr, false, 'style="width: 50%;"') . "&nbsp;\n";
            echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '');
            echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
            echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
            echo Form::closeForm();
            ?>
        </div>

</div>    
    
    
	<div class="row">
		<div id="yui-main" class="col-md-12">
			<div class="">
				<div class="">
					<div class="">
                    
                    
                    
                    <!--- page content --->
                    
                <div class="col-md-2" id="left_categories"><!--- left categories --->
                    <ul class="flat-categories">
                        <li><a href="#" id="folder_0"><?php echo Lang::t('_ALL_CATEGORIES', 'kb'); ?></a></li>
                        <?php foreach ($initial_folders['folders'] as $folder): ?>
                            <li><a href="#" id="folder_<?php echo $folder['id']; ?>"><?php echo $folder['name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                    
                    
						<div class="col-md-8">
							<!-- search form -->
							
                            

							
                            <!-- categories navigation -->
							<!-- Hiding Horizontal Cat navigation
							<ul id="kb_folder_nav" class="navigation"></ul>
							<div class="kb_folder_box"  id="folder_box">
								<ul id="kb_folder_box_ul" class="style_none"></ul>
								<div class="nofloat"></div>
							</div>
							-->
                            
							<!-- content table -->
                            <?php
									$this->widget('table', array(
										'id' => 'kb_table',
										'ajaxUrl' => 'ajax.server.php?r=kb/getlist',
										'rowsPerPage' => Get::sett('visuItem', 25),
										'startIndex' => 0,
										'results' => Get::sett('visuItem', 25),
										'sort' => 'r_name',
										'dir' => 'asc',
										'generateRequest' => 'KbManagement.requestBuilder',
										'events' => array(
											'postRenderEvent' => 'function () { lb.init(); }',
										),
										'columns' => array(
											array('key' => 'r_name', 'label' => Lang::t('_NAME', 'kb'), 'sortable' => true),
											array('key' => 'r_type', 'label' => Lang::t('_TYPE', 'kb'), 'sortable' => true, 'className' => 'img-cell'),
											//array('key' => 'r_env', 'label' => Lang::t('_ENVIRONMENT', 'kb'), 'sortable' => true),
											array('key' => 'r_env_parent', 'label' => Lang::t('_CONTAINED_IN', 'kb'), 'sortable' => false),
											array('key' => 'r_lang', 'label' => Lang::t('_LANGUAGE', 'kb'), 'sortable' => true),
											array('key' => 'tags', 'label' => Lang::t('_TAGS', 'kb'), 'sortable' => false),
											array('key' => 'play', 'label' => '<span class="ico-sprite subs_play"><span>' . Lang::t('_PLAY', 'storage') . '</span></span>', 'formatter' => 'frm_play', 'className' => 'img-cell'),
										),
										'fields' => array('res_id', 'r_name', 'r_type', 'r_env', 'r_env_parent', 'r_lang', 'tags', 'edit', 'force_visible', 'is_mobile'),
									)
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    
                                    );
							?>
                      
                            
								</div><!--- middle_colum --->
						
                        
                      
                        
                        
							<div class="col-md-2"><!--- tags column --->
								<p class="section_title"><?php echo Lang::t('_TAGS', 'kb'); ?></p>
								<?php if (!empty($tag_cloud)): ?>
									<ul class="tag_cloud" id="kb_tag_cloud">
									<?php foreach ($tag_cloud as $tag_id => $info): ?>
										<li class="t<?php echo $info['class_size']; ?>"><a href="#" id="tag_<?php echo $tag_id; ?>"><?php echo $info['tag_name']; ?></a></li>
									<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
                            
                            
                            
                            
                            
						</div>
					</div>
				</div> 

			</div>
			<div class="nofloat"></div>
<?php
									$this->widget('yuilog');
									$lmstab->endWidget(); 
?>
</div>