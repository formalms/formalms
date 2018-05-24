    <!-- welcome page (main tab) -->
    <div id="yui-main">
        <div class="yui-bx">
            <div>
                <?php
                $this->widget('lms_tab', array(
                    'active' => 'home'
                ));
                ?>
            </div>
            <div class="quick_search_form navbar forma-quick-search-form">
                <div id="tab_content"></div>
            </div>
        </div>
    </div>
    <div class="nofloat"></div>
<script type="text/javascript">
    document.getElementById('tab_content').innerHTML = '<?php echo addslashes($_content); ?>';
</script>

<style type="text/css">
    #tab_content {
        background: #fff;
        margin: 6px;
        text-align: left;
        padding: 1rem;
    }
</style>