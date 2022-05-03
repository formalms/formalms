<?php
echo Form::openForm('add_category', 'ajax.adm_server.php?r=alms/communication/add_category')

    . Form::getTextfield(
        Lang::t('_LABEL', 'communication'),
        'name',
        'name',
        255,
        ''
    )
    . Form::getHidden('id_category', 'id_category', '')
?>

<div id="translation_tab">
	<ul class="nav nav-tabs">
<?php
$_langs = Docebo::langManager()->getAllLanguages(true);
foreach ($_langs as $_lang_code => $_lang_data) {
    echo '<li' . ($_lang_code == getLanguage() ? ' class="active"' : '') . '>'
        . '<a data-toggle="tab" href="#langs_tab_' . $_lang_code . '"><em>' . $_lang_code . '</em></a>'
        . '</li>';
}
?>
	</ul>
	<div class="tab-content">
<?php
foreach ($_langs as $_lang_code => $_lang_data) {
    echo '<div class="tab-pane' . ($_lang_code == getLanguage() ? ' active' : '') . '" id="langs_tab_' . $_lang_code . '">'

        . Form::getSimpleTextarea(Lang::t('_DESCRIPTION', 'standard'),
                                    'translation_' . $_lang_code,
                                    'translation[' . $_lang_code . ']',
                                    '')
        . '</div>';
}
?>
	</div>
</div>
<?php echo Form::closeForm(); ?>