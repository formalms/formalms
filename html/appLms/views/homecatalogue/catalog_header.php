
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
          
                     var glob_serverUrl = "./appLms/ajax.server.php?r=homecatalogue/";
                     url = glob_serverUrl + "allCourseForma&id_cat=" + id_cat + "&type_course=" + type_course ; 
                     
                    // alert(glob_serverUrl );
                    
           $.ajax({
                      url: url                          
                    })
                   
                    
                      .done(function( data ) {
                        if ( console && console.log ) {
                          console.log( "risposta del ajax:", data );
                         $("#div_course").html(data); 
                        }
                      });
                                          
                    
                              
                      /*
                     var objAjax = YAHOO.util.Connect.asyncRequest('POST', url , {
                                success: function(objReq){
                                 try {
                                        var cat =objReq.responseText;
                                    } catch (e) {
                                           alert("errore ajax su calcolo catalogo")
                                     return; }
                                
                                    $("#div_course").html(objReq.responseText);
                                }
                            }); 
                      */      
                                            
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
                    <li>
                       <a href="index.php" class="home_cat_link">'.Lang::t('_BACK', 'standard').'</a>
                  </li>

                <ul> 
                </div>';   

                
   $down_menu = '<div class="tab_subnav">
                    <ul class="nav nav-pills" >
                        <li class="list-group-item">

                            <div class="form-group">
                                       <div class="col-md-5">
                                   
                                  <label for="gender1" class="col-sm-2 control-label"><i>'.Lang::t('_COURSE_TYPE', 'catalogue').'</i>:</label>
                                  </div>
                                      <div class="col-md-7">
                                      <select id="typeCourse" onchange="javascript:loadCourseType();" class="form-control">
                                          <option value="">'.Lang::t('_ALL').'</option>
                                          <option value="elearning">'.Lang::t('_ELEARNING').'</option>
                                          <option value="classroom">'.Lang::t('_CLASSROOM').'</option>
                                      </select>
                                  
                                    </div>
                                
                           </div> 
                       
      
                        
                         </li>                       
                        
                    </ul>
                    
                </div>
                    
                    ';

//    echo "<header class='header white-bg'>";                
    echo $up_menu;
    echo $down_menu;
//    echo "</header>";
?>

