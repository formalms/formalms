<div class="TreeWindow"></div>
<script>
     $(".TreeWindow").TreeWindow({
                    ajax: "index.php?r=lms/lo/get&id_course=<?php echo GET::req('id_course', DOTY_INT)?>&id=<?php echo GET::req('id', DOTY_INT) ?>",
                    folder_box: "<div><div class='title'></div></div>",
                    element_box: "<div><div class='title'></div></div>",
                    check_folder: function (data) {
                        return data.is_folder
                    },
                    fill_box_callback: function (is_folder, view, data) {
                        if(!is_folder){
                            if(data.link!=""){
                                view.find(".title").click(function(){
                                    alert('lo')
                                });
                            }
                        }else{ 
                            //alert('folder ='+'?id_item='+data.id+'&type='+data.type+'&resource='+data.resource)
                            view.find(".title").click(function(){
                                window.location = "?r=lms/lo/show&id_course=1&id="+data.id;
                            });
                        }
                        view.find(".title").html(data.title)
                    },
                    success: function(){

                        $(".TreeWindow").prepend("<div class='col-md-3'></div>");
                        $(".TreeWindow").prepend("<div class='col-md-3'></div>");
                    }
                });
</script>