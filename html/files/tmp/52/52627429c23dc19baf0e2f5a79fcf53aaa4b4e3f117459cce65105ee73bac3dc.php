<?php

/* base.html.twig */
class __TwigTemplate_a098d930aabe9c367692778eb1ce82e8fdad7a1ffc9e2dabad67d938417f4cf8 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'pagehead' => array($this, 'block_pagehead'),
            'stylesheet' => array($this, 'block_stylesheet'),
            'javascript' => array($this, 'block_javascript'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"";
        // line 2
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::lang_code"));
        echo "\">

<head>
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <!--Fix funzionamento scorm su IE9-->
    <!--[if lt IE 9]>
    <meta http-equiv=\"x-ua-compatible\" content=\"IE=8\"/>
    <![endif]-->     
    <!--END -->

    
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
    
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">


    <title>";
        // line 18
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::title"));
        echo "</title>
    ";
        // line 19
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "meta")));
        echo "
    ";
        // line 20
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::meta"));
        echo "
    <link rel=\"shortcut icon\" href=\"";
        // line 21
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "images/favicon.png\" type=\"image/png\"/>
    <link rel=\"shortcut icon\" href=\"";
        // line 22
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "images/favicon.ico\"/>
    <!-- reset and font stylesheet -->
    ";
        // line 24
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::resetter"));
        echo "                   
               
      
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src=\"https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js\"></script>
      <script src=\"https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js\"></script>
    <![endif]-->     
    
    <!-- Bootstrap JS -->
    <script src=\"";
        // line 34
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "resources/jquery/jquery-1.11.3.min.js\"></script>  
    <script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js\"></script>               
    
    <!--****************** CSS PER RESPONSIVE FORMA (LR)************************ -->   
    <!-- common stylesheet -->     
    <!-- Bootstrap CSS -->
    <link rel=\"stylesheet\" href=\"";
        // line 40
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/css/bootstrap.css\" />
    <link rel=\"stylesheet\" href=\"";
        // line 41
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/css/bootstrap-reset.css\" />
    
    <link rel=\"stylesheet\" href=\"";
        // line 43
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/css/bootstrap-theme.min.css\" />
    <link rel=\"stylesheet\" href=\"";
        // line 44
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/css/bootstrap.min.css\" />
     
    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 46
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/table-responsive.css\"/>

    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 48
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/responsive.css\"/>

         
    <link rel=\"stylesheet\" type=\"text/css\" href=\"http://tympanus.net/Tutorials/CaptionHoverEffects/css/default.css\" />
    <link rel=\"stylesheet\" type=\"text/css\" href=\"http://tympanus.net/Tutorials/CaptionHoverEffects/css/component.css\" />
    <script src=\"";
        // line 53
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/js/modernizr.custom.js\"></script>

         
     <!-- menus script menu swipe -->
    <script src=\"";
        // line 57
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/js/menu.js\"></script>
    <link rel=\"stylesheet\" href=\"http://callmenick.com/_development/slide-push-menus/css/font-awesome.min.css\">
    <link rel=\"stylesheet\" href=\"http://callmenick.com/_development/slide-push-menus/css/style.min.css\">         
       
     <!-- HELP DESK -->  
     <link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"";
        // line 62
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/helpdesk.css\">
     <link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"";
        // line 63
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/jquery.fancybox.css\">
     <script type=\"text/javascript\" src=\"";
        // line 64
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/js/jquery.min.js\"></script>
     <script type=\"text/javascript\" src=\"";
        // line 65
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/js/jquery.fancybox.js?v=2.0.6\"></script>     
     
     <link href=\"";
        // line 67
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/css/bootstrap-toggle.min.css\" rel=\"stylesheet\">
     <script src=\"";
        // line 68
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "bootstrap/js/bootstrap-toggle.min.js\"></script>

     
    <script src=\"";
        // line 71
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "script/helpdesk.js\"></script>                
    <script src=\"";
        // line 72
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "script/grid_list.js\"></script>          
               
                 
                  
      <!-- TOOLTIP -->
    <link href=\"";
        // line 77
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "resources/jquery/jquery-ui-1.11.4.min.css\" rel=\"stylesheet\">
    <script src=\"";
        // line 78
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "resources/jquery/jquery-ui-1.11.4.min.js\"></script>
  <script>
      \$(function() {
        \$( document ).tooltip();
      });
      </script>
      <style>
      label {
        display: inline-block;
        //width: 5em;
      }
  </style>   
   

    <!-- *************************************************** -->

    
    
    
    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 97
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/base.css\"/>    
    
    
    <!-- specific stylesheet -->
    ";
        // line 101
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("YuiLib::load", array(0 => "base")));
        echo "
    <!-- printer stylesheet-->
    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 103
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::path"));
        echo "style/print.css\" media=\"print\"/>
    ";
        // line 104
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::accessibility"));
        echo "
    <!-- Page Head area -->
    ";
        // line 106
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::zone", array(0 => "page_head")));
        echo "
    ";
        // line 107
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::rtl"));
        echo "
    ";
        // line 108
        $this->displayBlock('pagehead', $context, $blocks);
        // line 110
        echo "    
    <!-- Javascripts area -->
      
    ";
        // line 113
        $this->displayBlock('stylesheet', $context, $blocks);
        // line 115
        echo "    ";
        $this->displayBlock('javascript', $context, $blocks);
        // line 117
        echo "    
    

    
</head>
<body class=\"yui-skin-docebo yui-skin-sam\">
    <div class=\"container-fluid\">      
    ";
        // line 124
        $this->displayBlock('content', $context, $blocks);
        // line 126
        echo "        ";
        echo call_user_func_array($this->env->getFunction('evalPhp')->getCallable(), array("Layout::analytics"));
        echo "
    </div>
    
    
    <script>
    
      /**
       * Slide right instantiation and action.
       */
      var slideRight = new Menu({
        wrapper: '#o-wrapper',
        type: 'slide-right',
        menuOpenerClass: '.c-button',
        maskId: '#c-mask'
      });

      var slideRightBtn = document.querySelector('#c-button--slide-right');

      
      slideRightBtn.addEventListener('click', function(e) {
        e.preventDefault;
        slideRight.open();
      });

      
    </script>      
    
                          
</body>
</html>";
    }

    // line 108
    public function block_pagehead($context, array $blocks = array())
    {
        // line 109
        echo "    ";
    }

    // line 113
    public function block_stylesheet($context, array $blocks = array())
    {
        // line 114
        echo "    ";
    }

    // line 115
    public function block_javascript($context, array $blocks = array())
    {
        // line 116
        echo "    ";
    }

    // line 124
    public function block_content($context, array $blocks = array())
    {
        // line 125
        echo "    ";
    }

    public function getTemplateName()
    {
        return "base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  310 => 125,  307 => 124,  303 => 116,  300 => 115,  296 => 114,  293 => 113,  289 => 109,  286 => 108,  251 => 126,  249 => 124,  240 => 117,  237 => 115,  235 => 113,  230 => 110,  228 => 108,  224 => 107,  220 => 106,  215 => 104,  211 => 103,  206 => 101,  199 => 97,  177 => 78,  173 => 77,  165 => 72,  161 => 71,  155 => 68,  151 => 67,  146 => 65,  142 => 64,  138 => 63,  134 => 62,  126 => 57,  119 => 53,  111 => 48,  106 => 46,  101 => 44,  97 => 43,  92 => 41,  88 => 40,  79 => 34,  66 => 24,  61 => 22,  57 => 21,  53 => 20,  49 => 19,  45 => 18,  26 => 2,  23 => 1,);
    }
}
/* <!DOCTYPE html>*/
/* <html lang="{{ evalPhp('Layout::lang_code') }}">*/
/* */
/* <head>*/
/*     <meta name="viewport" content="width=device-width, initial-scale=1">*/
/*     <!--Fix funzionamento scorm su IE9-->*/
/*     <!--[if lt IE 9]>*/
/*     <meta http-equiv="x-ua-compatible" content="IE=8"/>*/
/*     <![endif]-->     */
/*     <!--END -->*/
/* */
/*     */
/*     <meta http-equiv="X-UA-Compatible" content="IE=edge">*/
/*     */
/*     <meta name="viewport" content="width=device-width, initial-scale=1.0">*/
/* */
/* */
/*     <title>{{ evalPhp('Layout::title') }}</title>*/
/*     {{ evalPhp('Layout::zone', ['meta']) }}*/
/*     {{ evalPhp('Layout::meta') }}*/
/*     <link rel="shortcut icon" href="{{ evalPhp('Layout::path') }}images/favicon.png" type="image/png"/>*/
/*     <link rel="shortcut icon" href="{{ evalPhp('Layout::path') }}images/favicon.ico"/>*/
/*     <!-- reset and font stylesheet -->*/
/*     {{ evalPhp('Layout::resetter') }}                   */
/*                */
/*       */
/*     <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->*/
/*     <!--[if lt IE 9]>*/
/*       <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>*/
/*       <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>*/
/*     <![endif]-->     */
/*     */
/*     <!-- Bootstrap JS -->*/
/*     <script src="{{ evalPhp('Layout::path') }}resources/jquery/jquery-1.11.3.min.js"></script>  */
/*     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>               */
/*     */
/*     <!--****************** CSS PER RESPONSIVE FORMA (LR)************************ -->   */
/*     <!-- common stylesheet -->     */
/*     <!-- Bootstrap CSS -->*/
/*     <link rel="stylesheet" href="{{ evalPhp('Layout::path') }}bootstrap/css/bootstrap.css" />*/
/*     <link rel="stylesheet" href="{{ evalPhp('Layout::path') }}bootstrap/css/bootstrap-reset.css" />*/
/*     */
/*     <link rel="stylesheet" href="{{ evalPhp('Layout::path') }}bootstrap/css/bootstrap-theme.min.css" />*/
/*     <link rel="stylesheet" href="{{ evalPhp('Layout::path') }}bootstrap/css/bootstrap.min.css" />*/
/*      */
/*     <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/table-responsive.css"/>*/
/* */
/*     <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/responsive.css"/>*/
/* */
/*          */
/*     <link rel="stylesheet" type="text/css" href="http://tympanus.net/Tutorials/CaptionHoverEffects/css/default.css" />*/
/*     <link rel="stylesheet" type="text/css" href="http://tympanus.net/Tutorials/CaptionHoverEffects/css/component.css" />*/
/*     <script src="{{ evalPhp('Layout::path') }}bootstrap/js/modernizr.custom.js"></script>*/
/* */
/*          */
/*      <!-- menus script menu swipe -->*/
/*     <script src="{{ evalPhp('Layout::path') }}bootstrap/js/menu.js"></script>*/
/*     <link rel="stylesheet" href="http://callmenick.com/_development/slide-push-menus/css/font-awesome.min.css">*/
/*     <link rel="stylesheet" href="http://callmenick.com/_development/slide-push-menus/css/style.min.css">         */
/*        */
/*      <!-- HELP DESK -->  */
/*      <link rel="stylesheet" type="text/css" media="all" href="{{ evalPhp('Layout::path') }}style/helpdesk.css">*/
/*      <link rel="stylesheet" type="text/css" media="all" href="{{ evalPhp('Layout::path') }}style/jquery.fancybox.css">*/
/*      <script type="text/javascript" src="{{ evalPhp('Layout::path') }}bootstrap/js/jquery.min.js"></script>*/
/*      <script type="text/javascript" src="{{ evalPhp('Layout::path') }}bootstrap/js/jquery.fancybox.js?v=2.0.6"></script>     */
/*      */
/*      <link href="{{ evalPhp('Layout::path') }}bootstrap/css/bootstrap-toggle.min.css" rel="stylesheet">*/
/*      <script src="{{ evalPhp('Layout::path') }}bootstrap/js/bootstrap-toggle.min.js"></script>*/
/* */
/*      */
/*     <script src="{{ evalPhp('Layout::path') }}script/helpdesk.js"></script>                */
/*     <script src="{{ evalPhp('Layout::path') }}script/grid_list.js"></script>          */
/*                */
/*                  */
/*                   */
/*       <!-- TOOLTIP -->*/
/*     <link href="{{ evalPhp('Layout::path') }}resources/jquery/jquery-ui-1.11.4.min.css" rel="stylesheet">*/
/*     <script src="{{ evalPhp('Layout::path') }}resources/jquery/jquery-ui-1.11.4.min.js"></script>*/
/*   <script>*/
/*       $(function() {*/
/*         $( document ).tooltip();*/
/*       });*/
/*       </script>*/
/*       <style>*/
/*       label {*/
/*         display: inline-block;*/
/*         //width: 5em;*/
/*       }*/
/*   </style>   */
/*    */
/* */
/*     <!-- *************************************************** -->*/
/* */
/*     */
/*     */
/*     */
/*     <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/base.css"/>    */
/*     */
/*     */
/*     <!-- specific stylesheet -->*/
/*     {{ evalPhp('YuiLib::load', ['base']) }}*/
/*     <!-- printer stylesheet-->*/
/*     <link rel="stylesheet" type="text/css" href="{{ evalPhp('Layout::path') }}style/print.css" media="print"/>*/
/*     {{ evalPhp('Layout::accessibility') }}*/
/*     <!-- Page Head area -->*/
/*     {{ evalPhp('Layout::zone', ['page_head']) }}*/
/*     {{ evalPhp('Layout::rtl') }}*/
/*     {% block pagehead %}*/
/*     {% endblock %}*/
/*     */
/*     <!-- Javascripts area -->*/
/*       */
/*     {% block stylesheet %}*/
/*     {% endblock %}*/
/*     {% block javascript %}*/
/*     {% endblock %}*/
/*     */
/*     */
/* */
/*     */
/* </head>*/
/* <body class="yui-skin-docebo yui-skin-sam">*/
/*     <div class="container-fluid">      */
/*     {% block content %}*/
/*     {% endblock %}*/
/*         {{ evalPhp('Layout::analytics') }}*/
/*     </div>*/
/*     */
/*     */
/*     <script>*/
/*     */
/*       /***/
/*        * Slide right instantiation and action.*/
/*        *//* */
/*       var slideRight = new Menu({*/
/*         wrapper: '#o-wrapper',*/
/*         type: 'slide-right',*/
/*         menuOpenerClass: '.c-button',*/
/*         maskId: '#c-mask'*/
/*       });*/
/* */
/*       var slideRightBtn = document.querySelector('#c-button--slide-right');*/
/* */
/*       */
/*       slideRightBtn.addEventListener('click', function(e) {*/
/*         e.preventDefault;*/
/*         slideRight.open();*/
/*       });*/
/* */
/*       */
/*     </script>      */
/*     */
/*                           */
/* </body>*/
/* </html>*/
