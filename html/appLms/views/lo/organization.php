<?php echo Form::openForm("orgshow" , "index.php?modname=storage&op=display", false, "POST"); ?>
    <div class="TreeWindow"></div>
<?php echo Form::closeForm(); ?>
<script>
    $(".TreeWindow").TreeWindow({
                    ajax: "index.php?r=lms/lo/get",
                    folder_box: "<div><div class='title'></div></div>",
                    element_box: "<div><div class='title'></div></div>",
                    check_folder: function (data) {
                        return data.is_folder
                    },
                    fill_box_callback: function (is_folder, view, data) {
                        if(!is_folder){
                            for (var action in data.actions) {
                                let element = data.actions[action]
                                $("<input>")
                                    .attr("type", "image")
                                    .attr("src", element.image)
                                    .attr("name", element.link)
                                    .appendTo(view)
                            }
                            if(data.link!=""){
                                view.find(".title").click(function(){
                                    alert(data.id)
                                });
                            }
                        }
                        view.find(".title").html(data.title)
                    },
                    success: function(){

                        $(".TreeWindow").prepend("<div class='col-md-3'></div>");
                        $(".TreeWindow").prepend("<div class='col-md-3'></div>");
                    }
                });
</script>