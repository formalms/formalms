<?php

/* home_login.html.twig */
class __TwigTemplate_dfd9fbac6c6f687e856006ff57fe523e2579c5334b24e774cb62d861bda20af1 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("base.html.twig", "home_login.html.twig", 1);
        $this->blocks = array(
            'stylesheet' => array($this, 'block_stylesheet'),
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
        echo "    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/lms-home.css\"/>
";
    }

    // line 7
    public function block_content($context, array $blocks = array())
    {
        // line 8
        echo "    <div class=\"header\">
        ";
        // line 9
        if (($this->getAttribute((isset($context["GLOBALS"]) ? $context["GLOBALS"] : null), "maintenance", array(), "array") != "on")) {
            // line 10
            echo "            <div class=\"select-language\">
                ";
            // line 11
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Lang::t", array(0 => "_CHANGELANG", 1 => "register")));
            echo ": ";
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::change_lang"));
            echo "
            </div>
        ";
        }
        // line 14
        echo "        <a href=\"index.php\"><img class=\"left_logo\" src=\"";
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "images/company_logo.png\"
                                 alt=\"Left logo\"/></a>

        <div class=\"nofloat\"></div>
    </div>
    <div class=\"content\">
        ";
        // line 20
        if ((($this->getAttribute($this->getAttribute((isset($context["GLOBALS"]) ? $context["GLOBALS"] : null), "framework", array(), "array"), "course_block", array(), "array") == "on") && ($this->getAttribute((isset($context["GLOBALS"]) ? $context["GLOBALS"] : null), "maintenance", array(), "array") != "on"))) {
            // line 21
            echo "            <div class=\"homecatalogue\">
                ";
            // line 22
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::get_catalogue"));
            echo "
            </div>
        ";
        }
        // line 25
        echo "        ";
        if (($this->getAttribute((isset($context["GLOBALS"]) ? $context["GLOBALS"] : null), "maintenance", array(), "array") != "on")) {
            // line 26
            echo "            <div class=\"login-box";
            if (call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("LoginLayout::isSocialActive"))) {
                echo "-social";
            }
            echo "\">
                <h2>LOGIN</h2>
                ";
            // line 28
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("LoginLayout::social_login"));
            echo "
                ";
            // line 29
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("LoginLayout::login_form"));
            echo "
                ";
            // line 30
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("LoginLayout::service_msg"));
            echo "
            </div>
        ";
        }
        // line 33
        echo "    </div>

    <!-- footer -->
    <div class=\"footer\">
        ";
        // line 37
        if (($this->getAttribute((isset($context["GLOBALS"]) ? $context["GLOBALS"] : null), "maintenance", array(), "array") != "on")) {
            // line 38
            echo "            ";
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "footer")));
            echo "
            ";
            // line 39
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("LoginLayout::links"));
            echo "
        ";
        }
        // line 41
        echo "        <div class=\"copyright\">
            ";
        // line 42
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::copyright"));
        echo "
        </div>
    </div>
    <div class=\"external_page\">";
        // line 45
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("LoginLayout::external_page"));
        echo "</div>
    <div class=\"webcontent\">
        ";
        // line 47
        if (($this->getAttribute((isset($context["GLOBALS"]) ? $context["GLOBALS"] : null), "maintenance", array(), "array") == "on")) {
            // line 48
            echo "        <div class=\"box\">
            <h3>";
            // line 49
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Lang::t", array(0 => "_MAINTENANCE", 1 => "configuration")));
            echo "</h3>

            <div class=\"text\">
                ";
            // line 52
            echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Lang::t", array(0 => "_MAINTENANCE", 1 => "login")));
            echo "
            </div>
        </div>
        ";
        }
        // line 56
        echo "
        <div class=\"box\">
            <h3>";
        // line 58
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Lang::t", array(0 => "_HOMEPAGE", 1 => "login")));
        echo "</h3>

            <div class=\"text\">
                ";
        // line 61
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Lang::t", array(0 => "_INTRO_STD_TEXT", 1 => "login")));
        echo "
            </div>
        </div>
    </div>
    <script type=\"text/javascript\">
        window.onload = function () {
            try {
                window.document.getElementById('login_userid').focus();
            } catch (e) {
            }
        }
    </script>
";
    }

    public function getTemplateName()
    {
        return "home_login.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  164 => 61,  158 => 58,  154 => 56,  147 => 52,  141 => 49,  138 => 48,  136 => 47,  131 => 45,  125 => 42,  122 => 41,  117 => 39,  112 => 38,  110 => 37,  104 => 33,  98 => 30,  94 => 29,  90 => 28,  82 => 26,  79 => 25,  73 => 22,  70 => 21,  68 => 20,  58 => 14,  50 => 11,  47 => 10,  45 => 9,  42 => 8,  39 => 7,  32 => 4,  29 => 3,  11 => 1,);
    }
}
/* {% extends "base.html.twig" %}*/
/* */
/* {% block stylesheet %}*/
/*     <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/lms-home.css"/>*/
/* {% endblock %}*/
/* */
/* {% block content %}*/
/*     <div class="header">*/
/*         {% if  GLOBALS['maintenance'] != "on" %}*/
/*             <div class="select-language">*/
/*                 {{ evalPhp('Lang::t', ['_CHANGELANG', 'register']) }}: {{ evalPhp('Layout::change_lang') }}*/
/*             </div>*/
/*         {% endif %}*/
/*         <a href="index.php"><img class="left_logo" src="{{ evalPhp('Layout::path') }}images/company_logo.png"*/
/*                                  alt="Left logo"/></a>*/
/* */
/*         <div class="nofloat"></div>*/
/*     </div>*/
/*     <div class="content">*/
/*         {% if  GLOBALS['framework']['course_block'] == "on" and GLOBALS['maintenance'] != "on" %}*/
/*             <div class="homecatalogue">*/
/*                 {{ evalPhp('Layout::get_catalogue') }}*/
/*             </div>*/
/*         {% endif %}*/
/*         {% if GLOBALS['maintenance'] != "on" %}*/
/*             <div class="login-box{% if evalPhp('LoginLayout::isSocialActive') %}-social{% endif %}">*/
/*                 <h2>LOGIN</h2>*/
/*                 {{ evalPhp('LoginLayout::social_login') | raw }}*/
/*                 {{ evalPhp('LoginLayout::login_form') | raw }}*/
/*                 {{ evalPhp('LoginLayout::service_msg') | raw }}*/
/*             </div>*/
/*         {% endif %}*/
/*     </div>*/
/* */
/*     <!-- footer -->*/
/*     <div class="footer">*/
/*         {% if GLOBALS['maintenance'] != "on" %}*/
/*             {{ evalPhp('Layout::zone', ['footer']) }}*/
/*             {{ evalPhp('LoginLayout::links') }}*/
/*         {% endif %}*/
/*         <div class="copyright">*/
/*             {{ evalPhp('Layout::copyright') }}*/
/*         </div>*/
/*     </div>*/
/*     <div class="external_page">{{ evalPhp('LoginLayout::external_page') }}</div>*/
/*     <div class="webcontent">*/
/*         {% if GLOBALS['maintenance'] == "on" %}*/
/*         <div class="box">*/
/*             <h3>{{ evalPhp('Lang::t', ['_MAINTENANCE', 'configuration']) }}</h3>*/
/* */
/*             <div class="text">*/
/*                 {{ evalPhp('Lang::t', ['_MAINTENANCE', 'login']) }}*/
/*             </div>*/
/*         </div>*/
/*         {% endif %}*/
/* */
/*         <div class="box">*/
/*             <h3>{{ evalPhp('Lang::t', ['_HOMEPAGE', 'login']) }}</h3>*/
/* */
/*             <div class="text">*/
/*                 {{ evalPhp('Lang::t', ['_INTRO_STD_TEXT', 'login']) }}*/
/*             </div>*/
/*         </div>*/
/*     </div>*/
/*     <script type="text/javascript">*/
/*         window.onload = function () {*/
/*             try {*/
/*                 window.document.getElementById('login_userid').focus();*/
/*             } catch (e) {*/
/*             }*/
/*         }*/
/*     </script>*/
/* {% endblock %}*/
