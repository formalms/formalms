<?php
  define("IS_AJAX", true);
  $a_node = json_encode($model->GetGlobalJsonTree());
  $id_cat = Get::req('id_cat', DOTY_INT, 0);
  cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/bootstrap-treeview.js', true), 'page_head');
  cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/catalog.js', true), 'page_head');
  
   // numero catalogo dichiarati
   $numero_catalogo = count($model->GetGlobalJsonTree());
  
?>
      
      
      
    <?php 
    // se non ci sono cataloghi, nascondere la colonna 
    if($numero_catalogo==0){  ?>  
          <div class="row">
               
                <div class="col-sm-12" id="div_course"><br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p></div>
          <div>
      <?php } else{ ?>
      
            <div class="row">
                <div class="col-sm-3">
                    <div id="treeview1" class=""></div>
                </div>    
                <div class="col-sm-9" id="div_course"><br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p></div>
            <div>
               
      <?php  }  ?>
      
      
      
      <script type="text/javascript">
              function callAjaxCatalog(id_cat) {
                  str_loading = "<?php echo Layout::path() ?>images/standard/loadbar.gif";
                  $("#div_course").html("<br><p align='center'><img src='"  + str_loading + "'></p>"); 
                  scriviCookie('id_current_cat',id_cat,60);
                  var type_course = leggiCookie('type_course');
                  var posting = $.get(
                             'ajax.server.php',
                             {
                              r:'catalog/allCourseForma',
                              id_cat:id_cat,
                              type_course: type_course,  
                              id_cata: <?php echo Get::req('id_cata', DOTY_INT, 0);   ?> ,
                             }
                  );
                  posting.done(function(responseText){
                    $("#div_course").html(responseText);    
                  });
                  posting.fail(function() {
                     $("#div_course").html('course catalogue load failed') 
                  })
              } 
              
          $(function() {
                  callAjaxCatalog(0)  
                  var category_tree = [
                      {
                          text: "&nbsp;&nbsp;<?php echo Lang::t('_ALL_COURSES') ?>",
                          href: "#Category",
                          id_cat: 0,
                          state: {
                            checked: true,
                            selected: true
                          },
                          showIcon:true ,
                          nodes:<?php echo $a_node ?>    
                      }    
                  ];
                  $("#treeview1").treeview({
                                data: category_tree,
                                enableLinks:false,
                                backColor: "#ffffff",
                                color: "#000000",
                                levels:2,
                                onhoverColor: '#F5F5F5'  ,
                                showTags: true,
                                multiSelect:false,
                                selectedBackColor: "#C84000",
                                
                                onNodeSelected: function(event, node) {
                                    id_category = node.id_cat;
                                    callAjaxCatalog(id_category);    
                                },
                                onNodeUnselected: function (event, node) {
                                    console.log("deselezionato");
                                }              
                  });
            }
          )    
          
          

                  
      </script>    
  
  
  
  

