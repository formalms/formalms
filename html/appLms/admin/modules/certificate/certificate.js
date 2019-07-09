//var list = [];
//var list_cert = [];
var totnum = 0;
var glob_dialog = null;
var successful_printed = [];
var all_ok = true, all_finished = false;

var signature;

var arr_id_users = [];
var arr_id_certificates = [];
var arr_course_id = [];

function push_arr_id_users(elem) {
    
    arr_id_users.push(elem);
    
}

function push_arr_id_certificates(elem) {
    
    arr_id_certificates.push(elem);
    
}

function push_arr_course_id(elem) {
    
    arr_course_id.push(elem);
    
} 

function set_signature(sig){
    
    signature = sig;
    
}          

function send_download(e, args) {
    if (args.type=="total") initializeTotalSelection(true);
    if (args.type=="single") initializeSingleSelection(args.scope, true);
    window.location = '//' + window.location.hostname + window.location.pathname + '?modname=certificate&op=send_zip_certificates&list='+list+'&list_cert='+list_cert+'&id_course='+glob_id_course;
}

function send_print(e, args) {
    YAHOO.util.Connect.asyncRequest('post', ajax_url, {
        success: function(oResponse) {
            var res = YAHOO.lang.JSON.parse(oResponse.responseText);

            glob_dialog = new YAHOO.widget.Dialog('print_popup', {
                width: "600px",
                fixedcenter: true,
                draggable: true,
                modal: true,
                close: false,
                visible: false,
                buttons: [ {id: 'dialog_print_certificate', text: _STOP, handler: handleStopEvent} ]
            });

            glob_dialog.setHeader(res.head);
            glob_dialog.setBody(res.body);

            glob_dialog.render(document.body);
            glob_dialog.show();

            if (args.type=="total") initializeTotalSelection(false, arr_id_certificates.length);
            if (args.type=="single") initializeSingleSelection(args.scope, false);
           // do_printing(list_cert[counter], glob_id_course, list[counter]);
           
            do_printing(arr_id_certificates[counter], arr_course_id[counter], arr_id_users[counter], arr_id_certificates.length);
        }
    }, 'op=getpopup');

}


function initializeTotalSelection(skipDialog, lenght_id_users) {
  
    counter = 0;

    if (skipDialog) {return;}
    if (lenght_id_users <= 0) {
        glob_dialog.destroy();
    } else {
        updateDialogNums(counter, lenght_id_users);
    }
}

function initializeSingleSelection(o, skipDialog) {
    var id = o.id.split('_')[2]; //retrieve user id
    list = [id];

    var id_cert = o.id.split('_')[3]; //retrieve certificate id
    list_cert = [id_cert];

    totnum = 1;
    counter = 0;
    if (skipDialog) {return;}
    updateDialogNums(counter, totnum);
}

//clear popup and adjust table rows and selection
function finalizeSelection() {
    all_finished = true;
    glob_dialog.cfg.queueProperty("buttons", [
		{ text: _close, handler: force_reload }
    ]);
    glob_dialog.render();
}

function array_contains(value, arr) {
    for (var i=0; i<arr.length; i++)
        if (arr[i] == value) return true;
    return false;
}

function updateDialogNums(counter, totnum) {
        YAHOO.util.Dom.get('actual_num').innerHTML = counter+'';
        YAHOO.util.Dom.get('total_num').innerHTML = totnum+'';  
}

function handleStopEvent() {
    this.destroy();
    if (all_finished) force_reload();
}

function do_printing(id_certificate, id_course, id_user, totnum)
{
    if (totnum <= 0) return;
    var res_el = YAHOO.util.Dom.get('print_result');
    YAHOO.util.Connect.asyncRequest('post', ajax_url, {
        success: function(oResponse)
        {
            var res, el = document.createElement("p");
            el.innerHTML = counter+')&nbsp;';
            try
            {
                res = YAHOO.lang.JSON.parse(oResponse.responseText);
            }
            catch(e)
            {
                el.className = "red";
                el.innerHTML += _ERROR_PARSE;
                all_ok = false;
                res = {success: false};
            }
            if (res.success)
            {
                el.className = "green";
                el.innerHTML += _SUCCESS;
                successful_printed.push(res.printed);
            }
            else
            {
                el.className = "red";
                el.innerHTML += res.message || "";
                all_ok = false;

                res_el.appendChild(el);
            }
        },
        customevents:
            {
                onComplete: function()
                {
                    var el = YAHOO.util.Dom.get('actual_num');
                    var num = Number(el.innerHTML);
                    el.innerHTML = num--;
                    counter++;
                    var pbar = YAHOO.util.Dom.get('print_progressbar');
                    pbar.style.width = Math.ceil( 100*(counter/totnum) )+'%';
                    updateDialogNums(counter,totnum);

                    if (counter<totnum)
                        do_printing(arr_id_certificates[counter], arr_course_id[counter], arr_id_users[counter], totnum);
                    else
                        finalizeSelection();
                }
            }
    }, 'op=print&id_certificate='+id_certificate+'&id_course='+id_course+'&id_user='+id_user);
}

function force_reload() {
    //window.location.href = reload_url;
    try {
        var tform = document.createElement("FORM");
        tform.method = "POST";
        tform.action = reload_url;

        var authentic_request = document.createElement("INPUT");
        authentic_request.type = "hidden";
        authentic_request.name = "authentic_request";
        //authentic_request.value = YAHOO.util.Dom.get("authentic_request_certificates_emission").value;
        authentic_request.value = signature;
        

        tform.appendChild(authentic_request);


        document.body.appendChild(tform);

        tform.submit();
    } catch(e) { alert(e); }
}

function reload() {
    if (all_ok) {
        force_reload();
    }
}
