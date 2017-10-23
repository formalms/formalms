var list = [];
var list_cert = [];
var totnum = 0;
var glob_dialog = null;
var successful_printed = [];
var all_ok = true, all_finished = false;

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

            if (args.type=="total") initializeTotalSelection(false);
            if (args.type=="single") initializeSingleSelection(args.scope, false);
            do_printing(list_cert[counter], glob_id_course, list[counter]);
        }
    }, 'op=getpopup');

}


function initializeTotalSelection(skipDialog) {
    //set selection of elements
    var sel = [];
    var sel_cert = [];

    var i, actual_sel = YAHOO.util.Selector.query('input[id^=selected_]');

    for (i=0; i<actual_sel.length; i++) {
        if (actual_sel[i].checked) {
            sel.push(actual_sel[i].value);
            sel_cert.push(actual_sel[i].getAttribute("id_certificate"));
        }
    }

    list = sel;
    list_cert = sel_cert;

    totnum = sel.length;
    counter = 0;

    if (skipDialog) {return;}
    if (totnum <= 0) {
        glob_dialog.destroy();
    } else {
        updateDialogNums(counter, totnum);
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

function do_printing(id_certificate, id_course, id_user)
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
                        do_printing(list_cert[counter], glob_id_course, list[counter]);
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
        authentic_request.value = YAHOO.util.Dom.get("authentic_request_certificates_emission").value;

        var filter = document.createElement("INPUT");
        filter.type = "hidden";
        filter.name = "filter";
        filter.value = YAHOO.util.Dom.get("active_text_filter").value;

        var only_released = document.createElement("INPUT");
        only_released.type = "hidden";
        only_released.name = "only_released";
        only_released.value = YAHOO.util.Dom.get("active_only_released").value;

        var active_ini = YAHOO.util.Dom.get("active_ini").value;
        var ini = document.createElement("INPUT");
        ini.type = "hidden";
        ini.name = "ini[" + active_ini + "]";
        ini.value = active_ini;

        tform.appendChild(filter);
        tform.appendChild(authentic_request);
        tform.appendChild(only_released);
        tform.appendChild(ini);

        document.body.appendChild(tform);

        tform.submit();
    } catch(e) { alert(e); }
}

function reload() {
    if (all_ok) {
        force_reload();
    } else {
        //...
    }
}

YAHOO.util.Event.onDOMReady(function() {
    YAHOO.util.Event.addListener(["print_selected_button_1", "print_selected_button_2"], "click", send_print, { scope: null, type: "total" });
    for (var i=0; i<single_list.length; i++) {
        YAHOO.util.Event.addListener(single_list[i], "click", send_print, { scope: YAHOO.util.Dom.get(single_list[i]), type: "single" } );
    }

    YAHOO.util.Event.addListener(["download_selected_button_1", "download_selected_button_2"], "click", send_download, { scope: null, type: "total" });

    YAHOO.util.Event.addListener(["select_all_1","select_all_2"], "click", function() {
        var i, list = YAHOO.util.Selector.query("input[id^=selected_]");
        for (i=0; i<list.length; i++) {
            list[i].checked = true;
        }
        old_el = YAHOO.util.Dom.get("old_selection");
        all_el = YAHOO.util.Dom.get("all_selection");
        var old_sel = old_el.value, all_sel = all_el.value;
        old_el.value = all_sel;
    });
    YAHOO.util.Event.addListener(["unselect_all_1","unselect_all_2"], "click", function() {
        var i, list = YAHOO.util.Selector.query("input[id^=selected_]");
        for (i=0; i<list.length; i++) {
            list[i].checked = false;
        }
        old_el = YAHOO.util.Dom.get("old_selection");
        old_el.value = "";
    });

});