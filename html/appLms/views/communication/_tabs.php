<div class="middlearea_container--margintop-small">
	<?php
    $lmstab = $this->widget('lms_tab', [
        'active' => 'communication',
        'close' => false,
    ]);
    ?>
	<div class="nested_tab" id="tab_content">
		<div id="global_conf" class="yui-navset yui-navset-top">
			<ul class="yui-nav">
				<?php if ($show_unread_tab) { ?>
				<li class="first <?php echo $active_tab == 'unread' ? 'selected' : ''; ?>">
					<a href="index.php?r=lms/communication/show">
						<em><?php echo Lang::t('_UNREAD', 'communication'); ?></em>
					</a>
				</li>
				<?php }
                if ($show_history_tab) { ?>
				<li class="<?php echo !$show_unread_tab ? 'first ' : ''; ?><?php echo $active_tab == 'history' ? 'selected' : ''; ?>">
					<a href="index.php?r=lms/communication/showhistory">
						<em><?php echo Lang::t('_HISTORY', 'communication'); ?></em>
					</a>
				</li>
				<?php } ?>
			</ul>
			<div class="yui-content">
				<?php
                // the tab are open in the content filder, now i can put my contents here easily
                $columns = [
                    ['key' => 'title', 'label' => Lang::t('_TITLE', 'communication'), 'sortable' => true],
                    ['key' => 'courseName', 'label' =>  Lang::t('_COURSE', 'course'), 'sortable' => true],
                    ['key' => 'categoryTitle', 'label' => Lang::t('_CATEGORY', 'communication'), 'sortable' => true],
                    ['key' => 'play', 'label' => Lang::t('_PLAY', 'standard')],
                ];

                $params = [
                    'id' => 'communication',
                    'ajaxUrl' => 'ajax.server.php?r=communication/' . $ajax_action,
                    'rowsPerPage' => FormaLms\lib\Get::sett('visuItem', 25),
                    'startIndex' => 0,
                    'results' => FormaLms\lib\Get::sett('visuItem', 25),
                    'sort' => 'title',
                    'dir' => 'asc',
                    'columns' => $columns,
                    'fields' => ['id_comm', 'title', 'courseName', 'categoryTitle', 'play'],
                    'events' => [
                        'postRenderEvent' => 'function() { lb.init(); }',
                    ],
                ];

                $this->widget('table', $params);
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
<script type="text/javascript">
	var lb = new LightBox();
</script>