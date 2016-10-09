<?php
echo getTitleArea($lang->def('_COURSEREPORT', 'menu_course'), 'coursereport');
?>
<div class="std_block">

    <div id="dhtmltooltip"></div>
    <style type="text/css">

        #dhtmltooltip {
            position: absolute;
            width: 150px;
            border: 2px solid black;
            padding: 2px;
            background-color: white;
            visibility: hidden;
            z-index: 100;
            filter: progid:DXImageTransform.Microsoft.Shadow(color=gray, direction=135);
        }
    </style>
    <script>
        var posx = 0;
        var posy = 0;
        document.onmousemove = function doSomething(e) {
            if (!e) e = window.event; // works on IE, but not NS (we rely on NS passing us the event)
            if (e) {
                if (e.pageX || e.pageY) {
                    posx = e.pageX;
                    posy = e.pageY;
                }
                else if (e.clientX || e.clientY) { // works on IE6,FF,Moz,Opera7
                    posx = e.clientX + document.body.scrollLeft;
                    posy = e.clientY + document.body.scrollTop;
                }
            }
        }

        var offsetxpoint = -60
        var offsetypoint = 20
        var ie = document.all
        var ns6 = document.getElementById && !document.all
        var enabletip = false
        if (ie || ns6)
            var tipobj = document.all ? document.all["dhtmltooltip"] : document.getElementById ? document.getElementById("dhtmltooltip") : ""

        function ietruebody() {
            return (document.compatMode && document.compatMode != "BackCompat") ? document.documentElement : document.body
        }

        function ddrivetip(thetext, thecolor, thewidth, pos, html) {
            if (ns6 || ie) {

                tipobj.innerHTML = '<?php echo $lang->def('_EXPORT'); ?> :' + html + '<a id="cambia_link" class="' + thetext + '" href="./index.php?modname=coursereport&op=export&amp;type_filter=<?php echo $lev; ?>"> <?php echo $lang->def('_EXPORT_STATS'); ?></a>";
                enabletip = true
                tipobj.style.width = "200px"
                tipobj.style.height = "auto"

                tipobj.style.left = posx + "px"
                tipobj.style.top = posy + "px"
                tipobj.style.visibility = "visible"
                return false
            }
        }

        function positiontip(e) {
            if (enabletip) {
                var curX = (ns6) ? e.pageX : event.x + ietruebody().scrollLeft;
                var curY = (ns6) ? e.pageY : event.y + ietruebody().scrollTop;

                var rightedge = ie && !window.opera ? ietruebody().clientWidth - event.clientX - offsetxpoint : window.innerWidth - e.clientX - offsetxpoint - 20
                var bottomedge = ie && !window.opera ? ietruebody().clientHeight - event.clientY - offsetypoint : window.innerHeight - e.clientY - offsetypoint - 20

                var leftedge = (offsetxpoint < 0) ? offsetxpoint * (-1) : -1000

                if (rightedge < tipobj.offsetWidth)

                    tipobj.style.left = ie ? ietruebody().scrollLeft + event.clientX - tipobj.offsetWidth + "px" : window.pageXOffset + e.clientX - tipobj.offsetWidth + "px"
                else if (curX < leftedge)
                    tipobj.style.left = "5px"
                else

                    tipobj.style.left = curX + offsetxpoint + "px"

                if (bottomedge < tipobj.offsetHeight)
                    tipobj.style.top = ie ? ietruebody().scrollTop + event.clientY - tipobj.offsetHeight - offsetypoint + "px" : window.pageYOffset + e.clientY - tipobj.offsetHeight - offsetypoint + "px"
                else
                    tipobj.style.top = curY + offsetypoint + "px"
                tipobj.style.visibility = "visible"
            }
        }

        function hideddrivetip() {
            if (ns6 || ie) {
                enabletip = false
                tipobj.style.visibility = "hidden"
                tipobj.style.left = "-1000px"
                tipobj.style.backgroundColor = ""
                tipobj.style.width = ""
            }
        }

        //document.onmousemove=positiontip

        var lista = new Array();
        function tool(arm, pos, htm) {

            var tipobj = document.getElementById("dhtmltooltip").style.visibility;
            if (tipobj == "visible") {
                hideddrivetip()
                lista = new Array();
            }
            else
                ddrivetip(arm, "", "", pos, htm);
            //else
            //tooltip.hide();
        }
        var url = "./index.php?modname=coursereport&op=export&amp;type_filter=<?php echo $lev; ?>";

        function cambialink(num, fare) {
            if (fare)
                lista[lista.length + 1] = num;
            else {
                i = 0;
                while (i < lista.length) {
                    if (lista[i] == num)
                        lista[i] = null;
                    i++;
                }
            }
            document.getElementById("cambia_link").href = url;//+document.getElementById("cambia_link").className;

            i = 0;
            stringaurl = "&aggiuntivi=";
            while (i < lista.length) {
                if (lista[i] != null)
                    stringaurl = stringaurl + lista[i] + ",";
                i++;
            }

            document.getElementById("cambia_link").href = document.getElementById("cambia_link").href + stringaurl;
            //alert(document.getElementById("cambia_link").href);
        }
    </script>
    ');