
<style >

.material-switch > input[type="checkbox"] {
    display: none;   
}

.material-switch > label {
    cursor: pointer;
    height: 0px;
    position: relative; 
    width: 40px;  
}

.material-switch > label::before {
    background: rgb(0, 0, 0);
    box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.5);
    border-radius: 8px;
    content: '';
    height: 16px;
    margin-top: -8px;
    position:absolute;
    opacity: 0.3;
    transition: all 0.4s ease-in-out;
    width: 40px;
}
.material-switch > label::after {
    background: rgb(255, 255, 255);
    border-radius: 16px;
    box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.3);
    content: '';
    height: 24px;
    left: -4px;
    margin-top: -8px;
    position: absolute;
    top: -4px;
    transition: all 0.3s ease-in-out;
    width: 24px;
}
.material-switch > input[type="checkbox"]:checked + label::before {
    background: inherit;
    opacity: 0.5;
}
.material-switch > input[type="checkbox"]:checked + label::after {
    background: inherit;
    left: 20px;
}


</style>



<script language="javascript">
   

   
     // carica corso in funzione della tipologia del corso selezionato 
    function loadCourseType(){
        
        
        
        var id_cat = leggiCookie('id_current_cat');
        var val_enroll = leggiCookie('val_enroll'); 
        var val_enroll_not = leggiCookie('val_enroll_not'); 

        str_loading = "<?php echo Layout::path() ?>images/standard/loadbar.gif";
        $("#div_course").html("<br><p align='center'><img src='"  + str_loading + "'></p>");         
        
         type_course  = document.getElementById("typeCourse").selectedIndex;
         if(type_course==0) get_type_curse = "";
         if(type_course==1) get_type_curse = "elearning";
         if(type_course==2) get_type_curse = "classroom";
        
        scriviCookie('type_course',get_type_curse,60);
          
           var objAjax = YAHOO.util.Connect.asyncRequest('POST', "ajax.server.php?r=catalog/allCourseForma&type_course=" + get_type_curse + "&id_cat=" + id_cat + "&val_enroll=" + val_enroll + "&val_enroll_not=" + val_enroll_not , {
                                success: function(objReq){
                                 try {
                                        var cat =objReq.responseText;
                                    } catch (e) {
                                           alert("errore ajax su calcolo catalogo")
                                     return; }
                                
                                    $("#div_course").html(objReq.responseText);
                                }
                            });                 
    }


    // carica corsi in funzione delle iscrizioni 
    function loadCourseEnroll(){
        
        str_loading = "<?php echo Layout::path() ?>images/standard/loadbar.gif";
        $("#div_course").html("<br><p align='center'><img src='"  + str_loading + "'></p>");         
        

        
          val_enroll = false;
      //  val_enroll = document.getElementById('someSwitchOptionDefault').checked
        val_enroll_not = document.getElementById('someSwitchOptionDefaultNot').checked

        
     //   scriviCookie('val_enroll',val_enroll,60);
        scriviCookie('val_enroll_not',val_enroll_not,60);
        
        var type_course = leggiCookie('type_course'); 
        var id_cat = leggiCookie('id_current_cat');

        var objAjax = YAHOO.util.Connect.asyncRequest('POST', "ajax.server.php?r=catalog/allCourseForma&type_course=" + type_course + "&id_cat=" + id_cat + "&val_enroll=" + val_enroll + "&val_enroll_not=" + val_enroll_not, {
                                success: function(objReq){
                                 try {
                                        var cat =objReq.responseText;
                                    } catch (e) {
                                           alert("errore ajax su calcolo catalogo")
                                     return; }
                                
                                    $("#div_course").html(objReq.responseText);
                                }
                            });   
        
    }
    

</script>

<style>

.show-on-hover:hover > ul.dropdown-menu {
    display: block;    
}
</style>


<?php

   $up_menu = '<div class="tabs-wrapper">
                <ul class="nav nav-tabs hidden-xs">
                    <li class="active">
                        <a href="#">'.Lang::t('_CATALOGUE').'</a>
                  </li>
                <ul> 
                </div>';   

                
   $down_menu = '<div class="tab_subnav">
                    <ul class="nav nav-pills" >
                        <li class="list-group-item">

                              <i>'.Lang::t('_COURSE_TYPE', 'catalogue').'</i>: 
                                  <select id="typeCourse" onchange="javascript:loadCourseType();">
                                      <option>'.Lang::t('_ALL').'</option>
                                      <option>'.Lang::t('_ELEARNING').'</option>
                                      <option>'.Lang::t('_CLASSROOM').'</option>
                                  </select>
                                &nbsp; &nbsp;
                                
                                                <!--
  
                                        <i>'.Lang::t('_COURSE_BEGIN','catalogue').'</i>
                                       &nbsp;<div class="material-switch pull-right">
                                            <input id="someSwitchOptionDefault" name="someSwitchOption001" type="checkbox" onclick="loadCourseEnroll()" />
                                            <label for="someSwitchOptionDefault" class="label-success"></label>
                                       
                                             -->
                                       
                                          &nbsp; &nbsp;&nbsp; &nbsp;&nbsp; 
                                        <i>Corsi a cui non sei iscritto</i>
                                            &nbsp;<div class="material-switch pull-right">
                                            <input id="someSwitchOptionDefaultNot" name="someSwitchOption00Not1" type="checkbox" onclick="loadCourseEnroll()" />
                                            <label for="someSwitchOptionDefaultNot" class="label-success"></label>
                                            </div>                                           
                                       
                                        </div>
                  
                                 
                                   
                       
      
                        
                         </li>                       
                        
                    </ul>
                    
                </div>
                    
                    ';

    echo $up_menu;
    echo $down_menu;
?>

