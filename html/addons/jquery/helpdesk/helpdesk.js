function validateEmail(email) { 
        var reg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return reg.test(email);
    }

    
    
   var getFlashVersion = function() {
        try {
            try {
                var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
                try {
                    axo.AllowScriptAccess = 'always';
                }
                catch (e) {
                    return '6,0,0';
                }
            }

            catch (e) {
            }

            return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];

        } catch (e) {
            try {
                if (navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin) {
                    return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
                }
            } catch (e) {
            }
        }
        return '0,0,0';
    }    
    
    
    var getScreenSize = function() {
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
    

     $(document).ready(function(){
      $('#close').on('click', function(){ 
            $.fancybox.close()
      });
 });
    
    $(document).ready(function() {
        $(".modalbox").fancybox();
        $("#contact").submit(function() { return false; });

        
        $("#send").on("click", function(){
            var emailval  = $("#email").val();
            var msgval    = $("#msg").val();
            var msglen    = msgval.length;
            var oggettoval    = $("#oggetto").val();
            var oggettolen    = oggettoval.length;
            var mailvalid = validateEmail(emailval);
            var msg_ok    = $("#msg_ok").val();
            
            var sendtoval  = $("#sendto").val();
            var sendtovalid = validateEmail(sendtoval);
      
            mailvalid ? $("#alphaemail").removeClass("error") : $("#alphaemail").addClass("error");
            sendtovalid ? $("#alphasendto").removeClass("error") : $("#alphasendto").addClass("error");
            msglen >= 4 ? $("#alphamsg").removeClass("error") : $("#alphamsg").addClass("error");
            oggettolen >= 1 ? $("#alphaoggetto").removeClass("error") : $("#alphaoggetto").addClass("error");
            
            if(mailvalid == true && msglen >= 4 && oggettolen >= 1 && sendtovalid == true) {
                // if both validate we attempt to send the e-mail
                // first we hide the submit btn so the user doesnt click twice
                $("#send").replaceWith("<em><img src=../templates/standard/images/tree/loading.gif ></em>");
                
                //** INFO CLIENT **
                flash_installed = ((typeof navigator.plugins != "undefined" && typeof navigator.plugins["Shockwave Flash"] == "object") || (window.ActiveXObject && (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) != false));
                if (flash_installed){
                    var version = getFlashVersion(); 
                    var vArr = version.split(',');
                    flash_installed = vArr[0];
                }
                screen_size = getScreenSize();    
                
                document.getElementById('help_req_resolution').value = 'width: '+screen_size.width+' height: '+screen_size.height;
                document.getElementById('help_req_flash_installed').value = flash_installed;                
                
                $.ajax({
                    type: 'POST',
                    url: 'ajax.server.php?r=helpdesk/show',
                    data: $("#contact").serialize(),
                    success: function(data) {
                        if(data == "true") {
                            $("#contact").fadeOut("fast", function(){
                                $(this).before("<p><strong>" + msg_ok + "</strong></p>");
                                setTimeout("$.fancybox.close()", 3000);
                            });
                        }
                    }
                });
            }
        });
    });