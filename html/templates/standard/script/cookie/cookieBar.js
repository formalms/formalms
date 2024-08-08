// apri modale
window.onload = function () {
    //openModalCookies();
}

function openModalCookies(){
    cookie_private = getCookie("cookie_private")

    $("#cookie_sys").prop("disabled",true);
    $("#id_alert").hide();

    $("#myModal").modal();
    $( "#accordion" ).accordion({
        heightStyle: "content"
    });


    if(cookie_private=="si") {
        $('#cookie_private').prop('checked', true).change()

    }
    if(cookie_private=="no"){
        $('#cookie_private').prop('checked', false).change()
    }

}

function saveCookies(){

    cookie_private =  $('#cookie_private').val();

    if ($('#cookie_private').is(":checked"))
    {
        cookie_private="si";
    }else{
        cookie_private="no";
    }



    setCookie("cookie_private",cookie_private,365)

    $("#id_alert").show();

}


function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    let expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

var sleepSetTimeout_ctrl;
function sleep(ms) {
    clearInterval(sleepSetTimeout_ctrl);
    return new Promise(resolve => sleepSetTimeout_ctrl = setTimeout(resolve, ms));
}

function test(){
    //document.getElementById("cookie_private").checked = true;
    //alert("test");
    //$('#cookie_private').prop('checked', true).change()


}


