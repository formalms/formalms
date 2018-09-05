
<?php
      
if (isset($id_policy)) {      
       echo getTitleArea("Modifica ". Lang::t('_PRIVACYPOLICIES', 'privacypolicies')); 

       echo '<div class="std_block">';
      

	$_form_id = 'mod_policy_form';
	$_form_action = 'index.php?r=adm/privacypolicy/mod_action';
} else {
	$_form_id = 'add_policy_form';
	//$_form_action = 'ajax.adm_server.php?r=adm/privacypolicy/add_action'; 
    $_form_action = 'index.php?r=adm/privacypolicy/add_action'; 
}

echo Form::openForm($_form_id, $_form_action,'',"POST");
      
echo Form::getTextfield(Lang::t('_NAME', 'standard'), 'policy_name', 'name', 255, (isset($id_policy) && isset($name) ? $name : ""));
echo Form::getCheckBox(Lang::t('_SET_AS_DEFAULT', 'standard'), 'is_default', 'is_default', 1, ($is_default == 1 ? true : false) );
echo Form::getCheckBox(Lang::t('_RESET_POLICY', 'standard'), 'reset_policy', 'reset_policy', 1, false);


//if we are editing an existent policy, print its id
if (isset($id_policy)) echo Form::getHidden('id_policy', 'id_policy', $id_policy);

echo '<div id="policy_langs_tab" class="yui-navset">';

$_tabview_titles = '<ul class="nav nav-tabs">';
$_tabview_contents = '<div class="tab-content">';
                    
//edit policy content in all languages
$_langs = Docebo::langManager()->getAllLanguages(true);
foreach ($_langs as $_lang_code => $_lang_data) {

            
	    $_translation = isset($id_policy) && isset($translations[$_lang_code]) ? $translations[$_lang_code] : "";
                   
	    $_tabview_titles .= '<li'.($_lang_code == Lang::get() ? ' class="active"' : '').'>'
		    .'<a data-toggle="tab" onclick="#"  href="#langs_tab_'.$_lang_code.'"><em>'.$_lang_code //$_lang_data['description']
		    .($_translation == '' && isset($id_policy) ? ' (*)' : '')
		    .'</em></a></li>';
    
            
        
        if($_lang_code == Lang::get()){
            $_tabview_contents .= '<div class="tab-pane in active"  id="langs_tab_'.$_lang_code.'" >';
        } else{   
	        $_tabview_contents .= '<div class="tab-pane"  id="langs_tab_'.$_lang_code.'" >';
        }
        
        
        $_tabview_contents .= Form::getTextarea(
		    $_lang_code,
		    'translation_'.$_lang_code,
		    'translation['.$_lang_code.']',
		    $_translation 
	    );

	    $_tabview_contents .= '</div>';
    
    

} //end for
                                                         
     

    if (isset($id_policy)) {   
        $_tabview_contents .= "<br>
            <div align='center'>     
                
      <button type='button' class='btn btn-success btn-sm'  onclick='javascript: send_privacy();'>
          <font color='#ffffff'><span class='glyphicon glyphicon-floppy-saved'></span> ".Lang::t('_SAVE', 'standard')."</font>
        </button>                
          
          
        <button type='button' class='btn btn-danger btn-sm' onclick='javascript:document.location=\"index.php?r=adm/privacypolicy/show\"'>
          <font color='#ffffff'><span class='glyphicon glyphicon-remove'></span> ".Lang::t('_CANCEL', 'standard')."</font>
        </button>          
                
            </div>
            ";
    }  else {
        
        
                $_tabview_contents .= "<br>
            <div align='center'>     
                
      <button type='button' class='btn btn-success btn-sm'  onclick='javascript: add_privacy();'>
          <font color='#ffffff'><span class='glyphicon glyphicon-floppy-saved'></span> ".Lang::t('_SAVE_BACK', 'report')."</font>
        </button>                
          
          
        <button type='button' class='btn btn-danger btn-sm' onclick='javascript:document.location=\"index.php?r=adm/privacypolicy/show\"'>
          <font color='#ffffff'><span class='glyphicon glyphicon-remove'></span> ".Lang::t('_CANCEL', 'standard')."</font>
        </button>          
                
            </div>
            ";
        
    }



$_tabview_titles .= '</ul>';
$_tabview_contents .= '</div>';

echo $_tabview_titles.$_tabview_contents;

echo '</div>';

echo Form::closeForm();


//if (isset($id_policy)) {  echo '</div>';}
echo '</div>';


?>


<script language="javascript">


function send_privacy(){
    
    for (i=0; i < tinyMCE.editors.length; i++){
        var content = tinyMCE.editors[i].getContent();  
        document.getElementById(tinyMCE.editors[i].id).value = content;
    }    
    
    
      
      mod_policy_form.submit();
    
}



function add_privacy(){
    
    for (i=0; i < tinyMCE.editors.length; i++){
        var content = tinyMCE.editors[i].getContent();  
        document.getElementById(tinyMCE.editors[i].id).value = content;
    }    
    
    
      
      add_policy_form.submit();
    
}



</script>



