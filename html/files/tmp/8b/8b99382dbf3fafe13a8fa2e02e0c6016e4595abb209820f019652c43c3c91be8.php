<?php

/* lms_user.html.twig */
class __TwigTemplate_2928212f74a862ad3c83395ee9ce16b766e347fefc6a9834ecd45c07296d06ac extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("base.html.twig", "lms_user.html.twig", 1);
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
        echo "    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/lms.css\"/>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 5
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/lms-to-review.css\"/>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 6
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/lms-menu.css\"/>
";
    }

    // line 9
    public function block_javascript($context, array $blocks = array())
    {
        // line 10
        echo "    <script type=\"text/javascript\" src=\"";
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Get::rel_path", array(0 => "base")));
        echo "/lib/js_utils.js\"></script>
";
    }

    // line 13
    public function block_content($context, array $blocks = array())
    {
        // line 14
        echo "    <!-- blind nav -->
    ";
        // line 15
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "blind_navigation")));
        echo "
    <!-- feedback -->
    ";
        // line 17
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "feedback")));
        echo "
    <!-- container -->
    <div id=\"container\">
        
        
        
        
        
        <!-- menu_over -->
        <div id=\"menu_over\" class=\"layout_menu_over\">
            ";
        // line 27
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::cart"));
        echo "
            ";
        // line 28
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "menu_over")));
        echo "
                    
        </div>
        
        
    
        
        <!-- content  -->   
        <div class=\"layout_colum_container\">
            ";
        // line 37
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "content")));
        echo "
            
            <div class=\"nofloat\"></div>
        </div>
            
        <!-- footer  -->
        <div id=\"footer\" class=\"layout_footer\">
            ";
        // line 44
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "footer")));
        echo "
            <div class=\"copyright\">
                ";
        // line 46
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::copyright"));
        echo "
            </div>
        </div>
        
        
        
        
    </div>
    <!-- scripts -->
    ";
        // line 55
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "scripts")));
        echo "
    <!-- debug -->
    ";
        // line 57
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "debug")));
        echo "
    <!-- def_lang -->
    ";
        // line 59
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "def_lang")));
        echo "
";
    }

    public function getTemplateName()
    {
        return "lms_user.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  135 => 59,  130 => 57,  125 => 55,  113 => 46,  108 => 44,  98 => 37,  86 => 28,  82 => 27,  69 => 17,  64 => 15,  61 => 14,  58 => 13,  51 => 10,  48 => 9,  42 => 6,  38 => 5,  33 => 4,  30 => 3,  11 => 1,);
    }
}
/* {% extends "base.html.twig" %}*/
/* */
/* {% block stylesheet %}*/
/*     <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/lms.css"/>*/
/*     <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/lms-to-review.css"/>*/
/*     <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/lms-menu.css"/>*/
/* {% endblock %}*/
/* */
/* {% block javascript %}*/
/*     <script type="text/javascript" src="{{ evalPhp('Get::rel_path', ['base']) }}/lib/js_utils.js"></script>*/
/* {% endblock %}*/
/* */
/* {% block content %}*/
/*     <!-- blind nav -->*/
/*     {{ evalPhp('Layout::zone', ['blind_navigation']) }}*/
/*     <!-- feedback -->*/
/*     {{ evalPhp('Layout::zone', ['feedback']) }}*/
/*     <!-- container -->*/
/*     <div id="container">*/
/*         */
/*         */
/*         */
/*         */
/*         */
/*         <!-- menu_over -->*/
/*         <div id="menu_over" class="layout_menu_over">*/
/*             {{ evalPhp('Layout::cart') }}*/
/*             {{ evalPhp('Layout::zone', ['menu_over']) }}*/
/*                     */
/*         </div>*/
/*         */
/*         */
/*     */
/*         */
/*         <!-- content  -->   */
/*         <div class="layout_colum_container">*/
/*             {{ evalPhp('Layout::zone', ['content']) }}*/
/*             */
/*             <div class="nofloat"></div>*/
/*         </div>*/
/*             */
/*         <!-- footer  -->*/
/*         <div id="footer" class="layout_footer">*/
/*             {{ evalPhp('Layout::zone', ['footer']) }}*/
/*             <div class="copyright">*/
/*                 {{ evalPhp('Layout::copyright') }}*/
/*             </div>*/
/*         </div>*/
/*         */
/*         */
/*         */
/*         */
/*     </div>*/
/*     <!-- scripts -->*/
/*     {{ evalPhp('Layout::zone', ['scripts']) }}*/
/*     <!-- debug -->*/
/*     {{ evalPhp('Layout::zone', ['debug']) }}*/
/*     <!-- def_lang -->*/
/*     {{ evalPhp('Layout::zone', ['def_lang']) }}*/
/* {% endblock %}*/
