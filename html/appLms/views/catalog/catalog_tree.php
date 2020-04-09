<?php
  define("IS_AJAX", true);

    /* managing setting options */
    $catalogue_todisplay = true;
    if (Get::sett('on_catalogue_empty')=='on') { // show user's catalogue or main catalogue
        if (count($user_catalogue)==0) {
           $starting_catalogue = 0;  // show main catalogue
        } else {
            if ($id_catalogue == 0) { 
                reset($user_catalogue);
                $key = key($user_catalogue);
                $starting_catalogue= $user_catalogue[$key]['idCatalogue'];
            } else {
                $starting_catalogue = $id_catalogue;
            }    
        }
    } else {   // show user's catalogue or nothing
        if (count($user_catalogue) > 0) { // show first user's catalogue
            if ($id_catalogue == 0) {
                reset($user_catalogue);
                $key = key($user_catalogue);
                $starting_catalogue= $user_catalogue[$key]['idCatalogue'];
            } else {
                $starting_catalogue = $id_catalogue; // or the called one
            }    
        } else {
            $catalogue_todisplay =  false;
        }
    }          
    


  
 
  
  
  $catalogue = $model->GetGlobalJsonTree($starting_catalogue);
  $a_node = json_encode($catalogue);
  cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/bootstrap-treeview.js', true), 'page_head');
  cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/catalog.js', true), 'page_head');
  
   // are there category ?
   $total_category = count($catalogue);
     
?>
      
      
      
    <?php 
    // if no category, no tree
    if($total_category==0){  ?>  
          <div class="row">
                <div class="col-sm-12" id="div_course"><br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p></div>
          <div>
      <?php } else{ ?>
      
            <div class="row">
                <div class="col-sm-4">
                    <div id="treeview1" class="aside"></div>
                </div>    
                <div class="col-sm-8" id="div_course"><br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p></div>
            <div>
               
      <?php  }  ?>


      <?php if ($catalogue_todisplay) { ?>
        <script type="text/javascript">
            var $treeview = $("#treeview1");

            function callAjaxCatalog(id_category) {

                <?php echo $no_course ?>
                str_loading = "<?php echo Layout::path() ?>images/standard/loadbar.gif";
                $("#div_course").html("<br><p align='center'><img src='" + str_loading + "'></p>");
                var type_course = getCurrentTypeCourse();
                var posting = $.get(
                    'ajax.server.php',
                    {
                        r: 'catalog/allCourseForma',
                        id_category: id_category,
                        type_course: type_course,
                        id_catalogue: <?php echo $starting_catalogue ?>
                    }
                );
                posting.done(function (responseText) {
                    $("#div_course").html(responseText);
                });
                posting.fail(function () {
                    $("#div_course").html('course catalogue load failed')
                })
            }

            function checkSticky() {
                var windowHeight = $( window ).height();
                var treeviewHeight = $treeview.innerHeight();
                $treeview.css({ maxHeight: windowHeight - 200, overflowY: 'auto'});
                $(".container-fluid").css("height", "auto");

                if (window.innerWidth >= 768 && $('#div_course').offset().top - $(window).scrollTop() <= 70) {
                    $treeview.css({ maxHeight: windowHeight - 190, overflowY: 'auto'});
                    $treeview.css({width: $treeview.parent().width(), position: 'fixed', top: '70px'});
                } else {
                    $treeview.css({position: 'static'});
                }
            }

            $(function () {
                callAjaxCatalog(0);
                a_node = <?php echo $a_node ?>

                // Alphabetical sort
                $.each(a_node, function(k, cat) {
                    if (cat.nodes) {
                        var result = Object.keys(cat.nodes).map(function(key) {
                            return cat.nodes[key];
                        });
                        result = result.sort(function(a, b) {
                            return (a['text'] < b['text']) ? -1 : (a['text'] > b['text']) ? 1 : 0;
                        })
                        cat.nodes = result;
                    }
                })
                var category_tree = [
                    {
                        text: "&nbsp;&nbsp;<?php echo Lang::t('_ALL_COURSES') ?>",
                        href: "#Category",
                        id_cat: 0,
                        state: {
                            checked: true,
                            selected: true
                        },
                        showIcon: true,
                        nodes:a_node
                    }
                ];
                $treeview.treeview({
                    data: category_tree,
                    enableLinks: false,
                    backColor: "#ffffff",
                    color: "#000000",
                    levels: 2,
                    onhoverColor: '#F5F5F5',
                    showTags: true,
                    multiSelect: false,
                    selectedBackColor: "#C84000",

                    onNodeSelected: function (event, node) {
                        var id_cat = node.id_cat ? node.id_cat : node.nodeId;

                        callAjaxCatalog(id_cat);
                    },
                    onNodeUnselected: function (event, node) {
                    }
                });

                // sticky feature
                checkSticky();
                window.addEventListener('scroll', checkSticky, true);
                window.addEventListener('resize', checkSticky, true);
            });
        </script>
        <?php } else { ?> 
            <script type="text/javascript">
                $("#div_course").html("<br><p align='center'><?php echo Lang::t('_NO_CATEGORY_TODISPLAY','catalogue')?></p>");
            </script>
        <?php } ?>            
            
        
  
  
  
  

