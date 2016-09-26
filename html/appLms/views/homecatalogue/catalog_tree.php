



<?php
  define("IS_AJAX", true);

  
 $a_node = json_encode($model->GetGlobalJsonTree());
 $id_cat = Get::req('id_cat', DOTY_INT, 0);
              
  cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/bootstrap-treeview.js', true), 'page_head');
  cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/catalog.js', true), 'page_head');

    cout(Util::get_js(Get::rel_path('lms') . '/views/homecatalogue/homecatalogue.js', true), 'page_head');
  
  ?>

  
                
      <div class="row">
            <div class="col-sm-3">
                <div id="treeview" class=""></div>
            </div>    
            <div class="col-sm-9" id="div_course"><br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p></div>
      
      <div>                
                
                

      <script type="text/javascript">
       
               callAjaxCatalog(0);
        
 
            // fun ajax per caricare i corsi della categoria                                      
               function callAjaxCatalog(id_cat){
                   
                  str_loading = "<?php echo Layout::path() ?>images/standard/loadbar.gif";
                  $("#div_course").html("<br><p align='center'><img src='" + str_loading + "'></p>"); 
                   
                  scriviCookie('id_current_cat',id_cat,60);
                  var type_course = "";          
                   

                     var glob_serverUrl = "./appLms/ajax.server.php?r=homecatalogue/";
                     url = glob_serverUrl + "allCourseForma&id_cat=" + id_cat + "&type_course=" + type_course;
                    
                    // JQUERY
                   
                    $.ajax({
                      url: url                          
                    })
                   
                    
                      .done(function( data ) {
                        if ( console && console.log ) {
                          console.log( "risposta del ajax:", data );
                         $("#div_course").html(data); 
                        }
                      });                    
                     
                    
                    // YAHOOO
                    /*
                    var objAjax = YAHOO.util.Connect.asyncRequest('POST', url, {
                                success: function(objReq){
                                 try {
                                        var cat =objReq.responseText;
                                    } catch (e) {
                                           alert("errore ajax su calcolo home catalogo")
                                     return; }
                                
                                    $("#div_course").html(objReq.responseText);
                                }
                            });                      
                      
                      */
                    
               } 
 
 
 
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
                          nodes :<?php echo $a_node  ?> 
                      }    
                      
                  ];
                 
                
                  $("#treeview").treeview({
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
                                   //  alert(id_category) ;
                                  
                                },
                                onNodeUnselected: function (event, node) {
                                    console.log("deselezionato");
                                    }              
                                                              
                  
                  
                  });
                                
   
          
          
          

          
         
      </script>    

  

