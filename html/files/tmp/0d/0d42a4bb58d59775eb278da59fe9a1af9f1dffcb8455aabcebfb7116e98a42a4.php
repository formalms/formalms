<?php

/* adm.html.twig */
class __TwigTemplate_18f3c7d90897da738ede2d7ea2944ec0bb86ae77896520ba9c231f911519c18f extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("base.html.twig", "adm.html.twig", 1);
        $this->blocks = array(
            'stylesheet' => array($this, 'block_stylesheet'),
            'javascript' => array($this, 'block_javascript'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_stylesheet($context, array $blocks = array())
    {
        // line 4
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"";
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/adm.css\"/>
<link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 5
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/lms-to-review.css\"/>
";
    }

    // line 8
    public function block_javascript($context, array $blocks = array())
    {
        // line 9
        echo "    <script type=\"text/javascript\">
        YAHOO.util.Event.onDOMReady(function () {
            checkSelect(document.getElementById('course_type').value);

            YAHOO.util.Event.on(
                YAHOO.util.Selector.query('select#course_type'), 'change', function (e) {
                    checkSelect(this.value);
                });

        });

        function checkSelect(val) {
            if (val == 'elearning') {
                document.getElementById(\"auto_subscription\").removeAttribute(\"disabled\");
            }
            else {
                document.getElementById(\"auto_subscription\").disabled = \"disabled\";
                document.getElementById(\"auto_subscription\").checked = false;
            }
        }
    </script>
";
    }

    // line 32
    public function block_content($context, array $blocks = array())
    {
        // line 33
        echo "<!-- blind nav -->
";
        // line 34
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "blind_navigation")));
        echo "
<!-- feedback -->
";
        // line 36
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "feedback")));
        echo "
<!-- container -->
<div id=\"admcontainer\">
    <!-- header -->
    <div id=\"header\" class=\"layout_header\">
        ";
        // line 41
        if ( !$this->getAttribute(call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Docebo::user")), "isAnonymous", array(), "method")) {
            // line 42
            echo "            <div class=\"user_panel\">
                <p>
                    ";
            // line 44
            if ( !$this->getAttribute(call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Docebo::user")), "isAnonymous", array(), "method")) {
                // line 45
                echo "                    <b><span> ";
                echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Lang::t", array(0 => "_WELCOME", 1 => "profile")));
                echo ", </span>
                        ";
                // line 46
                echo twig_escape_filter($this->env, $this->getAttribute(call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Docebo::user")), "getUserName", array(), "method"), "html", null, true);
                echo "</b>
                    ";
            }
            // line 48
            echo "                    <br/>
                    ";
            // line 49
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, "now", "Y-m-d H:i:s"), "html", null, true);
            echo "<br/>
                    <span class=\"select-language\">";
            // line 50
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::change_lang"));
            echo "</span>
                </p>
                ";
            // line 52
            if ( !$this->getAttribute(call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Docebo::user")), "isAnonymous", array(), "method")) {
                // line 53
                echo "                <ul>
                    <li><a class=\"identity\" href=\"index.php?r=lms/profile/show\">
                            <span>";
                // line 55
                echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Lang::t", array(0 => "_PROFILE", 1 => "profile")));
                echo "</span>
                        </a></li>
                    <li>
                        <a class=\"logout\" href=\"index.php?modname=login&amp;op=logout\">
                            <span>";
                // line 59
                echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Lang::t", array(0 => "_LOGOUT", 1 => "standard")));
                echo "</span>
                        </a></li>
                </ul>
                ";
            }
            // line 63
            echo "            </div>
        ";
        }
        // line 65
        echo "        <img class=\"left_logo\" src=\"";
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "images/company_logo.png\" alt=\"Left logo\"/>

        <div class=\"nofloat\"></div>
        ";
        // line 68
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "header")));
        echo "
    </div>
    <!-- menu_over -->
    ";
        // line 71
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "menu_over")));
        echo "
    <!-- content -->
    <div class=\"layout_colum_container\">
        ";
        // line 74
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "content")));
        echo "
        <div class=\"nofloat\"></div>
    </div>
</div>
<!-- footer -->
<div id=\"footer\" class=\"layout_footer\">
    ";
        // line 80
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "footer")));
        echo "
    <div class=\"copyright\">
        ";
        // line 82
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::copyright"));
        echo "
    </div>
</div>

<!-- scripts -->
";
        // line 87
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "scripts")));
        echo "
<!-- debug -->
";
        // line 89
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "debug")));
        echo "
<!-- def_lang -->
";
        // line 91
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "def_lang")));
        echo "
";
    }

    public function getTemplateName()
    {
        return "adm.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  196 => 91,  191 => 89,  186 => 87,  178 => 82,  173 => 80,  164 => 74,  158 => 71,  152 => 68,  145 => 65,  141 => 63,  134 => 59,  127 => 55,  123 => 53,  121 => 52,  116 => 50,  112 => 49,  109 => 48,  104 => 46,  99 => 45,  97 => 44,  93 => 42,  91 => 41,  83 => 36,  78 => 34,  75 => 33,  72 => 32,  47 => 9,  44 => 8,  38 => 5,  33 => 4,  30 => 3,  11 => 1,);
    }
}
/* {% extends "base.html.twig" %}*/
/* */
/* {% block stylesheet %}*/
/* <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/adm.css"/>*/
/* <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/lms-to-review.css"/>*/
/* {% endblock %}*/
/* */
/* {% block javascript %}*/
/*     <script type="text/javascript">*/
/*         YAHOO.util.Event.onDOMReady(function () {*/
/*             checkSelect(document.getElementById('course_type').value);*/
/* */
/*             YAHOO.util.Event.on(*/
/*                 YAHOO.util.Selector.query('select#course_type'), 'change', function (e) {*/
/*                     checkSelect(this.value);*/
/*                 });*/
/* */
/*         });*/
/* */
/*         function checkSelect(val) {*/
/*             if (val == 'elearning') {*/
/*                 document.getElementById("auto_subscription").removeAttribute("disabled");*/
/*             }*/
/*             else {*/
/*                 document.getElementById("auto_subscription").disabled = "disabled";*/
/*                 document.getElementById("auto_subscription").checked = false;*/
/*             }*/
/*         }*/
/*     </script>*/
/* {% endblock %}*/
/* */
/* {% block content %}*/
/* <!-- blind nav -->*/
/* {{ evalPhp('Layout::zone', ['blind_navigation']) }}*/
/* <!-- feedback -->*/
/* {{ evalPhp('Layout::zone', ['feedback']) }}*/
/* <!-- container -->*/
/* <div id="admcontainer">*/
/*     <!-- header -->*/
/*     <div id="header" class="layout_header">*/
/*         {% if not evalPhp('Docebo::user').isAnonymous() %}*/
/*             <div class="user_panel">*/
/*                 <p>*/
/*                     {% if not evalPhp('Docebo::user').isAnonymous() %}*/
/*                     <b><span> {{ evalPhp('Lang::t', ['_WELCOME', 'profile']) }}, </span>*/
/*                         {{ evalPhp('Docebo::user').getUserName() }}</b>*/
/*                     {% endif %}*/
/*                     <br/>*/
/*                     {{ "now"|date("Y-m-d H:i:s") }}<br/>*/
/*                     <span class="select-language">{{ evalPhp('Layout::change_lang') }}</span>*/
/*                 </p>*/
/*                 {% if not evalPhp('Docebo::user').isAnonymous() %}*/
/*                 <ul>*/
/*                     <li><a class="identity" href="index.php?r=lms/profile/show">*/
/*                             <span>{{ evalPhp('Lang::t', ['_PROFILE', 'profile']) }}</span>*/
/*                         </a></li>*/
/*                     <li>*/
/*                         <a class="logout" href="index.php?modname=login&amp;op=logout">*/
/*                             <span>{{ evalPhp('Lang::t', ['_LOGOUT', 'standard']) }}</span>*/
/*                         </a></li>*/
/*                 </ul>*/
/*                 {% endif %}*/
/*             </div>*/
/*         {% endif %}*/
/*         <img class="left_logo" src="{{ evalPhp('Layout::path') }}images/company_logo.png" alt="Left logo"/>*/
/* */
/*         <div class="nofloat"></div>*/
/*         {{ evalPhp('Layout::zone', ['header']) }}*/
/*     </div>*/
/*     <!-- menu_over -->*/
/*     {{ evalPhp('Layout::zone', ['menu_over']) }}*/
/*     <!-- content -->*/
/*     <div class="layout_colum_container">*/
/*         {{ evalPhp('Layout::zone', ['content']) }}*/
/*         <div class="nofloat"></div>*/
/*     </div>*/
/* </div>*/
/* <!-- footer -->*/
/* <div id="footer" class="layout_footer">*/
/*     {{ evalPhp('Layout::zone', ['footer']) }}*/
/*     <div class="copyright">*/
/*         {{ evalPhp('Layout::copyright') }}*/
/*     </div>*/
/* </div>*/
/* */
/* <!-- scripts -->*/
/* {{ evalPhp('Layout::zone', ['scripts']) }}*/
/* <!-- debug -->*/
/* {{ evalPhp('Layout::zone', ['debug']) }}*/
/* <!-- def_lang -->*/
/* {{ evalPhp('Layout::zone', ['def_lang']) }}*/
/* {% endblock %}*/
