<div class="TreeWindow"></div>
<script>
    $(".TreeWindow").TreeWindow({
                    ajax: "index.php?r=lms/lo/get&id_course=<?php echo GET::req('id_course', DOTY_INT)?>",
                    folder_box: "<div><div class='title'></div></div>",
                    element_box: "<div><div class='title'></div></div>",
                    check_folder: function (data) {
                        return data.is_folder
                    },
                    fill_box_callback: function (is_folder, view, data) {
                        if(!is_folder){
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