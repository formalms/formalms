function setCookie(cname,cvalue,cexpires_day,cpath)
{
  cpath = (typeof b !== 'undefined') ?  cpath : "/";
  var d = new Date();
  d.setTime(d.getTime() + (cexpires_day*24*60*60*1000));
  var expires = "expires="+d.toUTCString()
  document.cookie = cname + "=" + encodeURIComponent(cvalue) + ";" + expires + ";path="+cpath;
}          
     
     
     
function getCookie(cname)
{
  if (document.cookie.length > 0)
  {
     var cookie_array = decodeURIComponent(document.cookie).split(";");
    for (var i=0; i<cookie_array.length;i++) {
        var c = cookie_array[i];
        var cn=c.split("=");
        if (cn[0].charAt(0)==' ') {
            cn[0] = cn[0].slice(1)
        }
        if ( cn[0] == cname) {
            return cn[1];
        }

    }
  }
  return "";
}
