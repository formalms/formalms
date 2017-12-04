    <!-- welcome page (main tab) -->
    <div id="yui-main">
        <div class="yui-b">

            <div>
                <?php
                $this->widget('lms_tab', array(
                    'active' => 'home'
                ));
                ?>
            </div>
            <div class="quick_search_form navbar forma-quick-search-form"> 

            </div> 

        </div>
    </div>
    <div class="nofloat"></div>
<script type="text/javascript">
    //document.getElementById('tab_content').innerHTML = '<?php echo addslashes($_content); ?>';
</script>