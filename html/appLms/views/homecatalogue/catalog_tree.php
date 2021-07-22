<?php
define("IS_AJAX", true);
$a_node = json_encode($model->GetGlobalJsonTree());
$id_cat = Get::req('id_cat', DOTY_INT, 0);
cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/bootstrap-treeview.js', true), 'page_head');
cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/catalog.js', true), 'page_head');

cout(Util::get_js(Get::rel_path('lms') . '/views/homecatalogue/homecatalogue.js', true), 'page_head');

?>


<div class="row">
    <div class="col-sm-4">
        <div id="treeview1" class="aside"></div>
    </div>
    <div class="col-sm-8" id="div_course" style="padding-top: 15px;">
        <br>
        <p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>
    </div>
<div>


<script type="text/javascript">


    callAjaxCatalog(0);
    function callAjaxCatalog(id_cat) {

        str_loading = "<?php echo Layout::path() ?>images/standard/loadbar.gif";
        $("#div_course").html("<br><p align='center'><img src='" + str_loading + "'></p>");

        scriviCookie('id_current_cat', id_cat, 60);
        var type_course = "";
        var glob_serverUrl = "./appLms/ajax.server.php?r=homecatalogue/"; //preview

        url = glob_serverUrl + "allCourseForma&id_cat=" + id_cat + "&type_course=" + type_course;

        $.ajax({
            url: url
        })

        .done(function (data) {
            if (console && console.log) {
                $("#div_course").html(data);
            }
        });
    }


    var category_tree = [
        {
            text: " &nbsp;&nbsp;<?php echo Lang::t('_CATEGORY') ?>",
            href: "#Categoria",
            id_cat: 0,
            state: {
                checked: true,
                selected: true
            },
            showIcon: true,
            nodes:<?php echo $a_node  ?>
        }

    ];

    $("#treeview1").treeview({
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
            id_category = node.id_cat;
            callAjaxCatalog(id_category);

        },
        onNodeUnselected: function (event, node) {
            console.log("deselezionato");
        }
    });
</script>



