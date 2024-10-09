<?php
echo getTitleArea([
    'index.php?r=alms/label/show' => Lang::t('_LABELS', 'label'),
    Lang::t('_MOD', 'label'), ]);
?>
<div class="std_block">
<?php
echo Form::openForm('mod_label_form', 'index.php?r=alms/label/mod&amp;id_common_label=' . $id_common_label, false, false, 'multipart/form-data')
    . Form::openElementSpace()
    . Form::getFilefield(Lang::t('_IMAGE', 'label'), 'label_image', 'label_image')
    . ($model->getLabelFile($id_common_label) !== ''
        ? '<img style="height: 48px;" src="' . $GLOBALS['where_files_relative'] . '/appLms/label/' . $model->getLabelFile($id_common_label) . '" />'
            . Form::getCheckbox(Lang::t('_DEL_LABEL_IMAGE', 'label'), 'del_label_image', 'del_label_image', false)
        : '');
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
                . Form::getTextfield(Lang::t('_TITLE', 'label'), $lang_code . '_title', $lang_code . '_title', 255, (isset($label_info[$lang_code]) ? $label_info[$lang_code][LABEL_TITLE] : ''))
                . Form::getSimpleTextarea(Lang::t('_DESCRIPTION', 'label'), $lang_code . '_description', $lang_code . '_description', (isset($label_info[$lang_code]) ? $label_info[$lang_code][LABEL_DESCRIPTION] : ''))
                . '</div>';
        }
        ?>
	</div>
</div>
<script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function() {
        new YAHOO.widget.TabView("label_edit");

        var elTabList = YAHOO.util.Selector.query('div[id=label_edit] ul[class=yui-nav] li a');
        var elFormList = YAHOO.util.Selector.query('div[id^=langs_tab_]');
        YAHOO.util.Event.addListener(elTabList, "click", function(e) {
            var langName = ('textContent' in this ? this.textContent: this.innerText);

            for (i=0; i<elTabList.length; i++) {
                elTabList[i].parentElement.classList.remove("selected");
            }
            YAHOO.util.Dom.setAttribute(this.parentElement, 'class', 'selected');

            for (i=0; i<elFormList.length; i++) {
                if (elFormList[i].id == "langs_tab_" + langName) {
                    YAHOO.util.Dom.setStyle(elFormList[i], 'display', 'inline');
                } else {
                    YAHOO.util.Dom.setStyle(elFormList[i], 'display', 'none');
                }
            }
        });
    });
</script>
<?php
echo Form::closeElementSpace()
        . Form::openButtonSpace()
        . Form::getButton('update', 'update', Lang::t('_UPDATE', 'label'))
        . Form::getButton('undo', 'undo', Lang::t('_UNDO', 'label'))
        . Form::closeButtonSpace()
        . Form::closeForm();
?>
</div>