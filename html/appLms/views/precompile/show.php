<?php
echo getTitleArea(Lang::t('_PRECOMPILE', 'standard'));
?>
<div class="std_block">
<?php

echo $result_message;

echo Form::openForm('precompile_form', 'index.php?r=precompile/set', null, null, 'multipart/form-data');

if (!$fields_checked && FormaLms\lib\Get::sett('request_mandatory_fields_compilation', 'on') === 'on') {
    foreach ($fieldlist->getUserMandatoryFields(Forma::user()->getIdSt()) as $id_field => $m_field) {
        echo $fieldlist->playFieldForUser(Forma::user()->getIdSt(), $id_field, false, true);
    }
}

echo Form::getBreakRow();

echo '<div class="boxed">';
echo '<p class="privacy_policy">';
echo $policy_text;
echo '</p>';
echo Form::getBreakRow();
echo '<div class="align-center">';
echo Form::getHidden('policy_id', 'policy_id', $policy_id);
echo Form::getCheckbox(Lang::t('_ACCEPT', 'register'), 'accept_policy', 'accept_policy', 1, false);
echo '</div>';
echo '</div>';

echo Form::openButtonSpace();
echo Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'));
//echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'));
echo Form::closeButtonSpace();

echo Form::closeForm();

?>
</div>