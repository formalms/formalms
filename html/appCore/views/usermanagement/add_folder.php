<?php

$body = "";
$languages = Docebo::langManager()->getAllLanguages(true);//getAllLangCode();
$std_lang = getLanguage();

$body .= Form::openForm('addfolder_form', "ajax.adm_server.php?r=". $this->link."/createfolder");

$body .= Form::getHidden('addfolder_id_parent', 'id_parent', $id_parent);
$body .= Form::getTextfield(Lang::t('_CODE', 'organization_chart'), 'org_code', 'org_code', 50);
$body .= Form::getDropdown(Lang::t('_DEFAULTTEMPLATE', 'configuration'), 'associated_template', 'associated_template', getTemplateList(), $default_template);
$body .= Form::getBreakRow();

foreach ($languages as $language) {
	$lang_code = $language['code'];
	$lang_name = $language['description'];
	$body .= Form::getTextfield($lang_code, 'newfolder_'.$lang_code, 'langs['.$lang_code.']', 255);
}

$body.= "<hr>";

// adding custom fields (if any)

$vett_custom_org = $this->model->getCustomFieldOrg($id);
                        foreach ($vett_custom_org as $key => $value) {
                            $valueField = $this->model->getValueCustom($id ,$value['id_field']);  
                            if($value['type_field']=='dropdown'){
                                // recover field son of id_field
                                $vett_value_custom = $this->model->getLO_Custom_Value_Array($value['id_field']);
                                $body .= Form::getDropdown($value['translation'], 'custom_'.$value['id_field'], 'custom_'.$value['id_field'], $vett_value_custom,$valueField);                             
                            }
                            if($value['type_field']=='textfield'){
                                $body .= Form::getTextfield($value['translation'], 'custom_'.$value['id_field'], 'custom_'.$value['id_field'], 50,$valueField);                                    
                            }
                        }
            
$body .= "".Form::closeForm();

if (isset($json)) {
	$output['header'] = $title;
	$output['body'] = $body;
	echo $json->encode($output);
} else {
	echo '<h2>'.$title.'</h2>';
	echo $body;
}

?>