<script>
    function closePlayer() {
        document.removeChild(document.documentElement);
    };
</script>
<iframe src="./views/playerscorm/player.html?scorm=<?php echo $scorm; ?>&authentic_request=<?php echo Util::getSignature(); ?>"></iframe>
<style>
iframe {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 800px;
}
</style>