<?php
echo getTitleArea([
    'index.php?r=alms/label/show' => Lang::t('_LABELS', 'label'),
    Lang::t('_ADD', 'label'), ]);
?>
<div class="std_block">

<?php
echo Form::openForm('add_label_form', 'index.php?r=alms/label/add', false, false, 'multipart/form-data')
        . Form::openElementSpace()
        . Form::getFilefield(Lang::t('_IMAGE', 'label'), 'label_image', 'label_image');
?>
<div id="label_edit" class="yui-navset">
	<ul class="yui-nav">
		<?php
        foreach ($all_languages as $lang_code) {
            echo '<li' . ($lang_code == Lang::get() ? ' class="selected"' : '') . '>'
                . '<a href="#langs_tab_' . $lang_code . '"><em>' . $lang_code . '</em></a>'
                . '</li>';
        }
        ?>
	</ul>
	<div class="yui-content">
		<?php
        foreach ($all_languages as $lang_code) {
            echo '<div id="langs_tab_' . $lang_code . '"' . ($lang_code == Lang::get() ? '' : ' style="display: none;"') . '>'
                . Form::getTextfield(Lang::t('_TITLE', 'label'), $lang_code . '_title', $lang_code . '_title', 255)
                . Form::getSimpleTextarea(Lang::t('_DESCRIPTION', 'label'), $lang_code . '_description', $lang_code . '_description')
                . '</div>';
        }
        ?>
	</div>
</div>
<script type="text/javascript">
	new YAHOO.widget.TabView("label_edit");
    YAHOO.util.Event.onDOMReady(function() {
        var elTabList = YAHOO.util.Selector.query('div[id=label_edit] ul[class=yui-nav] li a');
        var elFormList = YAHOO.util.Selector.query('div[id^=langs_tab_]');
        YAHOO.util.Event.addListener(elTabList, "click", function(e) {
            var langName = this.innerHTML;

            for (i=0; i<elTabList.length; i++) {
                elTabList[i].parentElement.classList.remove("selected");
            }
            YAHOO.util.Dom.setAttribute(this, 'class', 'selected');

            for (i=0; i<elFormList.length; i++) {
                if (elFormList[i].id == "langs_tab_" + langName) {
                    elFormList[i].style.display = "inline";
                } else {
                    elFormList[i].style.display = "none";
                }
            }
        });
    });
</script>
<?php
echo Form::closeElementSpace()
        . Form::openButtonSpace()
        . Form::getButton('insert', 'insert', Lang::t('_INSERT', 'label'))
        . Form::getButton('undo', 'undo', Lang::t('_UNDO', 'label'))
        . Form::closeButtonSpace()
        . Form::closeForm();
?>

</div>