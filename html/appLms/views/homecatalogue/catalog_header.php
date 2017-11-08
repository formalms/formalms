
<style >

.material-switch > input[type="checkbox"] {
    display: none;   
}

.material-switch > label {
    cursor: pointer;
    height: 0px;
    position: relative; 
    width: 40px;  
}

.material-switch > label::before {
    background: rgb(0, 0, 0);
    box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.5);
    border-radius: 8px;
    content: '';
    height: 16px;
    margin-top: -8px;
    position:absolute;
    opacity: 0.3;
    transition: all 0.4s ease-in-out;
    width: 40px;
}
.material-switch > label::after {
    background: rgb(255, 255, 255);
    border-radius: 16px;
    box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.3);
    content: '';
    height: 24px;
    left: -4px;
    margin-top: -8px;
    position: absolute;
    top: -4px;
    transition: all 0.3s ease-in-out;
    width: 24px;
}
.material-switch > input[type="checkbox"]:checked + label::before {
    background: inherit;
    opacity: 0.5;
}
.material-switch > input[type="checkbox"]:checked + label::after {
    background: inherit;
    left: 20px;
}


</style>



<script language="javascript">
        
        function scriviCookie(nomeCookie,valoreCookie,durataCookie)
        {
          var scadenza = new Date();
          var adesso = new Date();
          scadenza.setTime(adesso.getTime() + (parseInt(durataCookie) * 60000));
          document.cookie = nomeCookie + '=' + escape(valoreCookie) + '; expires=' + scadenza.toGMTString() + '; path=/';
        }          
             
             
             
        function leggiCookie(nomeCookie)
        {
          if (document.cookie.length > 0)
          {
            var inizio = document.cookie.indexOf(nomeCookie + "=");
            if (inizio != -1)
            {
              inizio = inizio + nomeCookie.length + 1;
              var fine = document.cookie.indexOf(";",inizio);
              if (fine == -1) fine = document.cookie.length;
              return unescape(document.cookie.substring(inizio,fine));
            }else{
               return "";
            }
          }
          return "";
        }


   
     // select by course type
    function loadCourseType(){
         type_course  = document.getElementById("typeCourse").selectedIndex;
         if(type_course==0) get_type_curse = "";
         if(type_course==1) get_type_curse = "elearning";
         if(type_course==2) get_type_curse = "classroom";
         scriviCookie('type_course',get_type_curse,60);
         callAjaxCatalog(leggiCookie('id_current_cat'))
    }

   

</script>

<style>

.show-on-hover:hover > ul.dropdown-menu {
    display: block;    
}
</style>

<!--<a href="index.php">-->
<!--    <img class="left_logo" src="--><?php //echo Layout::path();?><!--images/company_logo.png" alt="Left logo" />-->
<!--</a>-->

<header class="header white-bg header--external-catalogue">
    <!-- Static navbar -->
    <nav>
        <div class="row-fluid" id="lms_menu_container">
            <div class="navbar-header">
                <a class="navbar-brand" href="?r=elearning/show&amp;sop=unregistercourse">
                    <img class="left_logo" src="<?php echo Layout::path(); ?>images/company_logo.png" alt="logo di sinistra">
                </a>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="glyphicon glyphicon-align-justify"></span>
                </button>
            </div>

            <div id="c-mask" class="c-mask"></div><!-- /c-mask overlay --></div>
        <!--/.nav-collapse -->
    </nav>

    <!-- /c-menu slide-right end profile right panel -->

</header>

<!--<div class="divider"></div>-->
<div class="tabs-wrapper">
    <ul class="nav nav-tabs hidden-xs">
        <li class="active">
            <a href="index.php?r=catalog/show&amp;id_cata=0">Catalogo Corsi</a>
        </li>
    </ul>
</div>
<div class="tab_subnav forma-quick-search-form">
    <ul class="nav nav-pills" >
        <li>
            <select class='form-control' id="typeCourse" onchange="javascript:loadCourseType();">
                <option value=''><?php echo Lang::t('_ALL') ?></option>
                <option value='elearning'><?php echo Lang::t('_ELEARNING') ?></option>
                <option value='classroom'><?php echo Lang::t('_CLASSROOM','classroom') ?></option>
            </select>
        </li>
    </ul>
</div>

<script type='text/javascript'>

      $("select#typeCourse").val(leggiCookie('type_course'))
</script>
                    


