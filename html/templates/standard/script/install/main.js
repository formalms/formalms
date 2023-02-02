
var busy = false; //acts like a 'mutex' so we have only one upload in progress at any given time
var arr = []; // bucket in which we copy the file chunks to mark them later on (isComplete)
var timer = 0;

/*JQUERY STEPS INSTANCE*/
let wizard = $("#installer-section").steps({
    headerTag: "h3",
    bodyTag: "section",
    transitionEffect: "slideLeft",
    autoFocus: true,
    stepsOrientation: $.fn.steps.stepsOrientation.vertical,
    startIndex: parseInt(startIndex),
      /* Labels */
    labels: {
        cancel: cancel,
        current: current,
        pagination: pagination,
        finish: finish,
        next: nextLabel,
        previous: prevLabel,
        loading: loading
    },
      onFinished: function (event, currentIndex) {
        $('a').off('click');
        $('a[role="menuitem"]').addClass('disabledActions');
        finalize();
   
    },
     onStepChanging: function (event, currentIndex, newIndex) {
      if(newIndex > currentIndex) {
        var result = validateStep(currentIndex + 1);
        if(!result) {
          setData('step', currentIndex);
        } else {
          formStore(currentIndex + 1);
          setData('step', newIndex);
        }

        return result;
      }
      //sto tornando indietro

   return true;
  }
});
/******************************************/

/*****FUNCTION TO VALIDATE EVERY STEP***********/
function validateStep(index) {
        
    switch(index) {
      case 1:
        return checkPrerequisites();
      break;
      case 2:
        return checkDbSettings();
      break;
      case 3:
        return checkAdminSettings();
      break;
      case 4:
        return checkSmtpSettings();
      break;
      default:
        return true;
        break;
    }
}

/*****************************/

/************FUNCTION TO RETRIEVE ELEMENT OF MUTEX ARRAY*********/
function currentArrIndex() {
    for (var i = 0; i < arr.length; i++) {
      if (arr[i].isComplete)
        continue;
    
      else 
        return i;
    }
}

/*************************************/


/************FUNCTION TO FINALIZE INSTALLATION*********/
function finalize() {
  
    let index = 1;
  
    $('.debug').val($("#loading").text());
    $('section.body').each(function() {
    
      arr.push({
        index: index,
        isComplete: false
      });
      
      index++;
    });

    timer = setTimeout(process, 1000);
}     

/*************************************/


/************FUNCTION TO PROCESS STEPS OF INSTALLATION*********/
function process() {

    let response = {};
    if (busy) {
      //we might hit here if a file takes longer than the current setTimeout interval (1sec)
      timer = setTimeout(process, 1000); //re-queue timer
    } else {

      if (currentArrIndex() < arr.length) {
        let xhrIndex = currentArrIndex()
        let postData = {'check': arr[xhrIndex].index};
        //check if is upgrade
        if($("#upgrade").length > 0) {
            postData.upgrade = true;
        }

        postData.debug = $("#debug").val();
        // So when ajax completes we'd mark this item as done and turn off busy 
        // so next item of arr will get processed in timer's next 'alarm'

        busy = true; // mark it as busy
        $.ajax({
          type: "POST",
          data: postData,
          async: false,
          url: window.frontend.config.url.base + "/appCore/ajax.adm_server.php?r=adm/install/finalize",
          success: function (data) {
            response = JSON.parse(data);
            arr[xhrIndex].isComplete = true;
            busy = false;
    
            result = response.success;
        
          },
          error: function (e) {
            alert("Error: \n" + e.status + " - " + e.statusText);
            return false;
          },
          });

        timer = setTimeout(process, 1000); //re-queue timer
      } else {
        clearTimeout(timer);
        $('a').on('click');
    
        $('a[role="menuitem"]').hide();
        $('a[role="menuitem"]').removeClass('disabledActions');
        $('.debug').val($('.debug').val() + "\n" + "END");
        $("#finalButtons").show();
        $("#success").show();
      }
    }
    var $debug = $('.debug').val();
    if(response.messages) {
      $('.debug').val($debug + "\n" + response.messages.join("\n") + "\n" + $("#loading").text());
    }
    
    //When sent, figure out the percentage uploaded and update the progress bar
    var percentage = 100 / arr.length * (currentArrIndex() + 1);
    $('.progress-bar').css('width', percentage + '%');

}
/*************************************/


/************FUNCTION TO CHECK SYSTEM REQUIREMENTS*********/
function checkPrerequisites() {

    var postData = {};
    if($("#checkRequirements").val() == 0) {
        postData.unsuitable_requirements = true;
    }

    //check if is upgrade
    if($("#upgrade").length > 0) {

        if($("#checkRequirements").val() && $("#upgrade").val() == "1") {
          return true;
        }

        postData.block_upgrade = true;
    } else {
        if($("#checkRequirements").val() == 1 && $('#agree').is(":checked")) {
            return true;
        } else {
        
      
        if(!$('#agree').is(":checked")) {
            postData.missing_check = true;
        }
      }
    
    }

    getErrorMessages(postData);
    return false;
}
/*************************************/


/************FUNCTION TO CHECK DB AND UPLOAD SETTINGS*********/
function checkDbSettings() {

    //check db connection
    return checkDbData();
}
/*************************************/


/************FUNCTION TO RETRIEVE ERROR MESSAGES*********/
function getErrorMessages(postData) {
    $.ajax({
        type: "POST",
        data: postData,
        url: window.frontend.config.url.base + "/appCore/ajax.adm_server.php?r=adm/install/getErrorMessages",
        success: function (data) {
          
            var response = JSON.parse(data);
            let text = response.messages.join("\n");
            alert(text);
        },
        error: function (e) {
            alert("Error: \n" + e.status + " - " + e.statusText);
            return false;
        },
    });
}
/*************************************/


/************FUNCTION TO CHECK DB RELATIVE DATA*********/
function checkDbData() {
    var postData = {};
    var result = false;
    $("#database_data input").each(function(e) {
        postData[$(this).attr('name')] = $(this).val();
    });
       
    $.ajax({
        type: "POST",
        data: postData,
         async: false,
        url: window.frontend.config.url.base + "/appCore/ajax.adm_server.php?r=adm/install/checkDbData",
        success: function (data) {
          
            var response = JSON.parse(data);
            if(!response.success) {
                let text = response.messages.join("\n");
                alert(text);
            }
            
            result = response.success;
         
        },
        error: function (e) {
            alert("Error: \n" + e.status + " - " + e.statusText);
            return false;
        },
    });

    return result;
}
/*************************************/



/************FUNCTION TO SET DATA IN SESSION*********/
function setData(key, value) {
      
    var postData = {
      [key] : value
    };
       
    $.ajax({
        type: "POST",
        data: postData,
        url: window.frontend.config.url.base + "/appCore/ajax.adm_server.php?r=adm/install/set",
        success: function (data) {
          
          var result = JSON.parse(data);
          if(result.success == true && key == 'sel_lang') {
            location.reload();
          } 
        },
        error: function (e) {
        alert("Error: \n" + e.status + " - " + e.statusText);
        return false;
        },
    });
}
/*************************************/


/************FUNCTION TO CHECK ADMIN SETTINGS*********/
function checkAdminSettings() {
    let postData = {};
    var result = false;
    $("#adminPanel input").each(function(e) {

        postData[$(this).attr('name')] = $(this).val();
    });

    $.ajax({
        type: "POST",
        data: postData,
           async: false,
        url: window.frontend.config.url.base + "/appCore/ajax.adm_server.php?r=adm/install/checkAdminData",
        success: function (data) {
          
          var response = JSON.parse(data);
          if(!response.success) {
             let text = response.messages.join("\n");
            alert(text);
          }
         
         result = response.success;


        },
        error: function (e) {
        alert("Error: \n" + e.status + " - " + e.statusText);
        return false;
        },
    });

    return result;
     
}
/*************************************/


/************FUNCTION TO CHECK SMTP DATA*********/
function checkSmtpSettings() {
    let postData = {};
    var result = false;

    if($("#useSmtp").val() == 'off') {
        return true;
    } else {
    $("#smtpPanel .required").each(function(e) {

        postData[$(this).attr('name')] = $(this).val();
    });

    $.ajax({
        type: "POST",
        data: postData,
           async: false,
        url: window.frontend.config.url.base + "/appCore/ajax.adm_server.php?r=adm/install/checkSmtpData",
        success: function (data) {
          
            var response = JSON.parse(data);
            if(!response.success) {
                let text = response.messages.join("\n");
            alert(text);
            }
            
            result = response.success;
        },
        error: function (e) {
            alert("Error: \n" + e.status + " - " + e.statusText);
            return false;
        },
    });

      return result;
    }
      
}
/*************************************/


/************FUNCTION TO CHECK SMTP DATA*********/
function formStore(index) {

    if($("#formstep"+index).length > 0) {
        var form = $("#formstep"+index).serializeArray();
    
        var postData = JSON.parse(JSON.stringify(form));
        $.ajax({
            type: "POST",
            data: postData,
            async: false,
            url: window.frontend.config.url.base + "/appCore/ajax.adm_server.php?r=adm/install/formSave",
            success: function (data) {
                
            var response = JSON.parse(data);
            result = response.success;


            },
            error: function (e) {
                alert("Error: \n" + e.status + " - " + e.statusText);
                return false;
            },
        });
    }
}
/*************************************/


/************ATTACHING EVENTS*********/


$("#language").on("change", function(e) {
    e.preventDefault();
    setData('sel_lang', $(this).val());
});
    
$("#agree").on("click", function(e) {
    setData($(this).attr('name'), $(this).is(":checked"));
});

$("input[type=text]").on("focusout", function(e) {
    setData($(this).attr('name'), $(this).val());

});

$("input[type=password]").on("focusout", function(e) {
    setData($(this).attr('name'), $(this).val());

});


$(".lang_to_install_list input[type=checkbox]").on("click", function(e) {
    var key = null;
    if($(this).is(":checked")) {
        key = 'selectLangs';
    } else {
        key = 'deselectLangs';
    }

    setData(key, $(this).val());

});

$("#smtpPanel select").on("change", function(e) {

    setData($(this).attr('name'), $(this).val());
});

/***********************************/

function testMigrations() {

  var result = false;
  let postData = {};
  postData['debug'] = $("#debug").val();
  postData['upgrade'] = $("#upgrade").val();
  $.ajax({
    type: "POST",
    data: postData,
    async: false,
    url: window.frontend.config.url.base + "/appCore/ajax.adm_server.php?r=adm/install/testMigrations",
    success: function (data) {
        
    var response = JSON.parse(data);
    result = response.success;

    var $debug = $('.debug').val();
    if(response.messages) {
      $('.debug').val($debug + "\n" + response.messages.join("\n") + "\n");
    }


    },
    error: function (e) {
        alert("Error: \n" + e.status + " - " + e.statusText);
        return false;
    },
});
}