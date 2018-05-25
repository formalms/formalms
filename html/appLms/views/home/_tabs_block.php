<!-- welcome page (main tab) -->
<div id="yui-main">
    <div class="yui-a">
        <div>
            <?php
            $this->widget('lms_tab', array(
                'active' => 'home'
            ));
            ?>
        </div>
    </div>
    <div id="tab_content"></div>
</div>

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
    #tab_content h1 {
        padding-bottom: 1rem;
    }
</style>