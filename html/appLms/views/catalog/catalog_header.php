<?php
     
     
     
     function ShowCatalogue($user_catalogue){
         if (Get::sett('on_catalogue_empty')=='on' ) {
             if (count($user_catalogue)==0) {
                 showGeneralCatalogueTab();
             } else {
                 showUserCatalogueTab($user_catalogue);   
             }    
          } else {
            if (count($user_catalogue) > 0) {
                 showUserCatalogueTab($user_catalogue);
            } else {
                showEmptyCatalogueTab();
            }
             
         }
     }
     // display assigned catalogues
     function showUserCatalogueTab($user_catalogue){
             $i = 0;
             foreach ($user_catalogue as $id_catalogue => $cat_info){
                 if ((is_null($_GET['id_catalogue']) && $i ==0 ) || (intval($_GET['id_catalogue'])==$id_catalogue) ){
                     $active = "class='active'";
                     $i = 1;
                     $current_catalogue_id =  $id_catalogue;
                     echo PHP_EOL.'<script>current_catalogue='.$id_catalogue.';</script>'.PHP_EOL;
                 }
                 echo '<li '.$active.'>'
                 . '<a href="index.php?r=catalog/show&amp;id_catalogue=' . $id_catalogue . '">'
                 . '' . $cat_info['name'] . ''
                 . '</a>'
                 . '</li>';
                 $active = '';
             }
             return;  
     }
     
     // display general catalogue                
     function showGeneralCatalogueTab(){
         echo '<li class="active"><a href="index.php?r=catalog/show&amp;id_catalogue=0">'.Lang::t('_CATALOGUE').'</a></li>'.
             PHP_EOL.'<script>current_catalogue=0;</script>'.PHP_EOL; 
          
      }
      
      function showEmptyCatalogueTab(){
            echo '<li class="active">'
                 . '<a href="#">'
                 . Lang::t('_CATALOGUE')
                 . '</a>'
                 . '</li>';  
      }
                     
 ?>
 
 
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
 
 .show-on-hover:hover > ul.dropdown-menu {
     display: block;    
 }
 
  #toTop{
     position: fixed;
     bottom: 60px;
     right: 30px;
     cursor: pointer;
     display: none;
     
 } 
 
 
 </style>
 
 
 <script language="javascript">
  
      
     function getCurrentTypeCourse() {
        c = getCookie('catalog['+current_catalogue+'].type_course'); 
        return (c =='' ? '': c ) 
     }
 
     
     $(document).ready(function(){
           $('body').append('<div id="toTop" class="btn btn-info"><span class="glyphicon glyphicon-chevron-up"></span><?php echo Lang::t('_BACKTOTOP','faq') ?></div>');
             $(window).scroll(function () {
                 if ($(this).scrollTop() != 0) {
                     $('#toTop').fadeIn();
                 } else {
                     $('#toTop').fadeOut();
                 }
             }); 
         $('#toTop').click(function(){
             $("html, body").animate({ scrollTop: 0 }, 1000);
             return false;
         });
 
         
         
         $("select#typeCourse").val(getCurrentTypeCourse())
         
         $('#typeCourse').change(function(){
             setCookie('catalog['+current_catalogue+'].type_course',this.value, 60);
             callAjaxCatalog(getCookie('id_current_category'))
         })
 
         
     });                                                                                                                                       
   
  </script>  
 
 
 <div class="tabs-wrapper">
                 <ul id="catalog_nav" class="nav nav-tabs hidden-xs">
                       <?php 
                             ShowCatalogue($user_catalogue); 
                       ?>
                 </ul>            
                 
 </div>
 <div class="tab_subnav">
             <ul class="nav nav-pills" >
                 <li>
                           <select class='form-control' id="typeCourse">
                               <option value=''><?php echo Lang::t('_ALL') ?></option>
                               <option value='elearning'><?php echo Lang::t('_ELEARNING', 'catalogue') ?></option>
                               <option value='classroom'><?php echo Lang::t('_CLASSROOM_COURSE','cart') ?></option>
                           </select>
                  </li>                       
             </ul>
 </div>
 