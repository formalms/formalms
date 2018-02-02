<style>

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
        position: absolute;
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

    .show-on-hover:hover > ul.dropdown-menu {
        display: block;
    }

    #toTop {
        position: fixed;
        bottom: 60px;
        right: 30px;
        cursor: pointer;
        display: none;

    }


</style>


<script language="javascript">

    function scriviCookie(nomeCookie, valoreCookie, durataCookie) {
        var scadenza = new Date();
        var adesso = new Date();
        scadenza.setTime(adesso.getTime() + (parseInt(durataCookie) * 60000));
        document.cookie = nomeCookie + '=' + escape(valoreCookie) + '; expires=' + scadenza.toGMTString() + '; path=/';
    }


    function leggiCookie(nomeCookie) {
        if (document.cookie.length > 0) {
            var inizio = document.cookie.indexOf(nomeCookie + "=");
            if (inizio != -1) {
                inizio = inizio + nomeCookie.length + 1;
                var fine = document.cookie.indexOf(";", inizio);
                if (fine == -1) fine = document.cookie.length;
                return unescape(document.cookie.substring(inizio, fine));
            } else {
                return "";
            }
        }
        return "";
    }


    // select by course type
    function loadCourseType() {
        type_course = document.getElementById("typeCourse").selectedIndex;
        if (type_course == 0) get_type_curse = "";
        if (type_course == 1) get_type_curse = "elearning";
        if (type_course == 2) get_type_curse = "classroom";
        scriviCookie('type_course', get_type_curse, 60);
        callAjaxCatalog(leggiCookie('id_current_cat'))
    }

    $("select#typeCourse").val(leggiCookie('type_course'))

    $(document).ready(function () {
        $('body').append('<div id="toTop" class="btn btn-info"><span class="glyphicon glyphicon-chevron-up"></span><?php echo Lang::t('_BACKTOTOP', 'faq') ?></div>');
        $(window).scroll(function () {
            if ($(this).scrollTop() != 0) {
                $('#toTop').fadeIn();
            } else {
                $('#toTop').fadeOut();
            }
        });
        $('#toTop').click(function () {
            $("html, body").animate({scrollTop: 0}, 1000);
            return false;
        });
    });

</script>


<div class="tabs-wrapper">
    <ul class="nav nav-tabs hidden-xs">
        <?php


        if (Get::sett('on_catalogue_empty') == 'on') {
            if (count($user_catalogue) == 0) {
                echo '<li class="active" ><a href="index.php?r=catalog/show&amp;id_cata=0">' . Lang::t('_CATALOGUE') . '</a></li>';
            } else {
                GetMyFirstCatalogue($user_catalogue);
            }
        } else {
            if (!$catalogue_exist) {
                echo '<li class="active" ><a href="index.php?r=catalog/show&amp;id_cata=0">' . Lang::t('_CATALOGUE') . '</a></li>';
            } else {
                if (count($user_catalogue) > 0) {
                    GetMyFirstCatalogue($user_catalogue);
                }
            }
        }


        function GetMyFirstCatalogue($user_catalogue)
        {
            $i = 0;
            foreach ($user_catalogue as $id_catalogue => $cat_info) {
                if ((is_null($_GET['id_cata']) && $i == 0) || (intval($_GET['id_cata']) == $id_catalogue)) {
                    $active = "class='active'";
                    $i = 1;
                }
                echo '<li ' . $active . ' >'
                    . '<a href="index.php?r=catalog/show&amp;id_cata=' . $id_catalogue . '">'
                    . '' . $cat_info['name'] . ''
                    . '</a>'
                    . '</li>';
                $active = '';
            }
            return;
        }

        ?>

    </ul>

</div>
<div class="tab_subnav">
    <ul class="nav nav-pills">
        <li>
            <select class='form-control' id="typeCourse" onchange="javascript:loadCourseType();">
                <option value=''><?php echo Lang::t('_ALL') ?></option>
                <option value='elearning'><?php echo Lang::t('_ELEARNING') ?></option>
                <option value='classroom'><?php echo Lang::t('_CLASSROOM', 'classroom') ?></option>
            </select>
        </li>
    </ul>
</div>
