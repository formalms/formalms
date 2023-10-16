$(document).ready(function() {

    $('#_HELPDESK').click(function() {
        $('#_HELPDESK').attr('href', '#');
        $('#send_request').hide();
        $("#disclaimer").prop("checked", false);
        $('#modal_helpdesk').modal('show');
    })


    $("#disclaimer").change(function() {
        if ($("#disclaimer").is(":checked")) {
            $("#send_request").show();
        } else {
            $("#send_request").hide();
        }
    });

    $("#contact").submit(function() { return false; });

    $("#send_request").on("click", function(){
        $('#modal_helpdesk').modal('hide');

        var emailval  = $("#email").val();
        var msgval    = $("#msg").val();
        var msglen    = msgval.length;
        var oggettoval    = $("#oggetto").val();
        var oggettolen    = oggettoval.length;
        var mailvalid = validateEmail(emailval);

        var sendtoval  = $("#sendto").val();
        var sendtovalid = validateEmail(sendtoval);

        mailvalid ? $("#email").removeClass("error") : $("#email").addClass("error");
        msglen >= 4 ? $("#amsg").removeClass("error") : $("#msg").addClass("error");
        oggettolen >= 1 ? $("#alphaoggetto").removeClass("error") : $("#oggetto").addClass("error");

        oggettolen <1 ?  $('#div_err_obj').html(window.frontend.config.lang.translations.customer_help._OBJ_MISSING) : $('#div_err_obj').html("");
        oggettolen <1 ?  $('#div_err_obj').addClass("alert alert-danger") : $('#div_err_obj').removeClass("alert alert-danger");

        msglen < 4    ?  $('#div_err_msg').html(window.frontend.config.lang.translations.customer_help._MSG_MISSING) : $('#div_err_msg').html("");
        msglen < 4    ?  $('#div_err_msg').addClass("alert alert-danger") : $('#div_err_msg').removeClass("alert alert-danger");


        if(mailvalid == true && msglen >= 4 && oggettolen >= 1 && sendtovalid == true) {

            screen_size = getScreenSize();

            document.getElementById('help_req_resolution').value = 'width: '+screen_size.width+' height: '+screen_size.height;
            var formData = new FormData();
            formData.append('sendto', $("#sendto").val());
            formData.append('email', $("#email").val());
            formData.append('msg', $("#msg").val());
            formData.append('telefono', $("#telefono").val());
            formData.append('username', $("#username").val());
            formData.append('oggetto', $("#oggetto").val());
            formData.append('priorita', $("#priorita").val());
            formData.append('help_req_resolution', $("#help_req_resolution").val());


            $.ajax({
                type: 'POST',
                url: 'ajax.server.php?r=helpdesk/show',
                data: formData,
                cache: false,
                contentType: false,
                processData: false ,
                success: function(data) {
                },
                error: function(error) {
                }
            });


        }
    });
});


function validateEmail(email) {
    var reg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return reg.test(email);
}


function getScreenSize () {
    var mql = window.matchMedia("(orientation: portrait)");

    // If there are matches, we're in portrait
    if(mql.matches) {
        // Portrait orientation
        return {width: screen.width, height: screen.height};
    } else {
        // Landscape orientation
        return {width: screen.height, height: screen.width};
    }
}
