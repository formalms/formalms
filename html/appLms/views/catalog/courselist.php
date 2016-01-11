

    <div class="well well-sm">
        <strong><?php echo Lang::t('_CATALOGUE', 'standard'); ?></strong>
        <div class="btn-group">
            <a href="#" id="list" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-th-list">
            </span><?php echo Lang::t('_SWITCH_TO_LIST', 'link'); ?></a> <a href="#" id="grid" class="btn btn-default btn-sm"><span
                class="glyphicon glyphicon-th"></span>Grid</a>
        </div>
    </div>


<?php
	echo	$nav_bar->getNavBar()
			//.$html
            
            
            .' <div id="products" class="row list-group">'
    
            .$html
            .'</div>'
            
			.$nav_bar->getNavBar();
			// #1995 Grifo multimedia LR
?>

<script type="text/javascript">
    var lb = new LightBox();
    lb.back_url = 'index.php?r=lms/catalog/show&sop=unregistercourse';
    
    var Config = {};
    Config.langs = {_CLOSE: '<?php echo Lang::t('_CLOSE', 'standard'); ?>'};
    lb.init(Config);  
</script>

<!-- GRIGLIA -->
  <script type="text/javascript">          
$(document).ready(function() {
    $('#list').click(function(event){event.preventDefault();$('#products .item').addClass('list-group-item');});
    $('#grid').click(function(event){event.preventDefault();$('#products .item').removeClass('list-group-item');$('#products .item').addClass('grid-group-item');});
});

 </script>


