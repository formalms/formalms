<?php
  define("IS_AJAX", true);
 /* GLOBAL $children;
  GLOBAL $the_tree;
  GLOBAL $tree_deep;

  $global_tree = [];
  $top_category = $model->getMajorCategory();
  foreach ($top_category as $a_top_cat_key=>$val) {
        $tree_deep = 0;
        $the_tree = [];
        $children = $model->getMinorCategoryTree($a_top_cat_key);
        GetChildTree(array_keys($children));
        $global_tree[] = array('text'=>$the_tree[0]['text'], 'nodes'=>$the_tree[0]['nodes']);
  }     

   function GetChildTree($array_k) {
       GLOBAL $the_tree;
       GLOBAL $children;
       GLOBAL $tree_deep;
       $leaves = [];       
       foreach ($array_k as $single_key) {
           if (is_array($children[$single_key]['son'])) {
               $tree_deep++;
               $b = GetChildTree(array_keys($children[$single_key]['son']));
               $leaves[] = array('text'=>$children[$single_key]['name'], 'nodes'=>$b);
               if  ($tree_deep==0){
                    $the_tree[] = array('text'=>$leaves[0]['text'], 'nodes'=>$leaves[0]['nodes']);
                    $tree_deep = 0;                   
               }
           } else {
                $leaves[] = array('text'=>$children[$single_key]['name']);
           }
           if (array_key_exists($single_key, $children)) {
                    unset($children[$single_key]);   
           }
       }
       $tree_deep--;
       return $leaves;
   }
 
  */
  
 $a_node = json_encode($model->GetGlobalJsonTree());
 $id_cat = Get::req('id_cat', DOTY_INT, 0);
 
 //echo  json_encode($all_nodes);
   
  
  cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/bootstrap-treeview.js', true), 'page_head');
  cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/catalog.js', true), 'page_head');
  ?>
  
  

      <div class="row">
            <div class="col-sm-3">
                <div id="treeview1" class=""></div>
            </div>    
            <div class="col-sm-9" id="div_course"><br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p></div>
      
      <div>
      
      
      <script type="text/javascript">
        
            <?php
             //if($id_cat==0) echo "callAjaxCatalog(0)";
            ?>
        
    
            callAjaxCatalog(0);
        
            // fun ajax per caricare i corsi della categoria                                      
               function callAjaxCatalog(id_cat){
                   
                  str_loading = "<?php echo Layout::path() ?>images/standard/loadbar.gif";
                  $("#div_course").html("<br><p align='center'><img src='"  + str_loading + "'></p>"); 
                   
                  scriviCookie('id_current_cat',id_cat,60);
                  var type_course = leggiCookie('type_course');       
                  var val_enroll = leggiCookie('val_enroll');              
                  var val_enroll_not = leggiCookie('val_enroll_not');    
                   
                   
                    var objAjax = YAHOO.util.Connect.asyncRequest('POST', "ajax.server.php?r=catalog/allCourseForma&id_cat=" + id_cat + "&type_course=" + type_course + "&val_enroll=" + val_enroll + "&val_enroll_not=" + val_enroll_not, {
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
        
               
          $(function() {  
                  var category_tree = [
                      {
                          text: "<?php echo Lang::t('_CATEGORY') ?>",
                          href: "#Categoria",
                          
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
                                    console.log("selezionato " + node);
                                        
                                    id_category = node.id_cat;

                                    // chiamo ajax per caricare i corsi della categoria                                                                      
                                     callAjaxCatalog(id_category);    
                                 
                                  
                                },
                                onNodeUnselected: function (event, node) {
                                    console.log("deselezionato");
                                    }              
                                });
            }
            
           
            
          )    
          
          
          
          
          
        function scriviCookie(nomeCookie,valoreCookie,durataCookie)
        {
          var scadenza = new Date();
          var adesso = new Date();
          scadenza.setTime(adesso.getTime() + (parseInt(durataCookie) * 60000));
          document.cookie = nomeCookie + '=' + escape(valoreCookie) + '; expires=' + scadenza.toGMTString() + '; path=/';
        }          
             
             
             
        function leggiCookie(nomeCookie)
        {
          if (document.cookie.length > 0)
          {
            var inizio = document.cookie.indexOf(nomeCookie + "=");
            if (inizio != -1)
            {
              inizio = inizio + nomeCookie.length + 1;
              var fine = document.cookie.indexOf(";",inizio);
              if (fine == -1) fine = document.cookie.length;
              return unescape(document.cookie.substring(inizio,fine));
            }else{
               return "";
            }
          }
          return "";
        }
                  
      </script>    
  
  
  
  

