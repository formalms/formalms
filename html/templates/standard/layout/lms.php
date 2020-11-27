<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Layout::lang_code(); ?>">
	<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->     
    
    <!-- Bootstrap JS -->
    <script src="<?php echo Layout::path(); ?>resources/jquery/jquery-1.11.3.min.js"></script>  
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>               
    
    

    
    <!--****************** CSS PER RESPONSIVE FORMA (LR)************************ --> 
    <!-- common stylesheet -->     
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo Layout::path(); ?>bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="<?php echo Layout::path(); ?>bootstrap/css/bootstrap-reset.css">
    
    <link rel="stylesheet" href="<?php echo Layout::path(); ?>bootstrap/css/bootstrap-theme.min.css">   
    <link rel="stylesheet" href="<?php echo Layout::path(); ?>bootstrap/css/bootstrap.min.css">

     
    <link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/table-responsive.css"/>

    <link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/responsive.css"/>

         
        <link rel="stylesheet" type="text/css" href="http://tympanus.net/Tutorials/CaptionHoverEffects/css/default.css" />
        <link rel="stylesheet" type="text/css" href="http://tympanus.net/Tutorials/CaptionHoverEffects/css/component.css" />
        <script src="<?php echo Layout::path(); ?>bootstrap/js/modernizr.custom.js"></script>
<!--        <script src="--><?php //echo Layout::path(); ?><!--bootstrap/js/demoad.js"></script>-->
         
         
         
     <!-- menus script menu swipe -->
    <script src="<?php echo Layout::path(); ?>bootstrap/js/menu.js"></script>
    <link rel="stylesheet" href="http://callmenick.com/_development/slide-push-menus/css/font-awesome.min.css">
    <link rel="stylesheet" href="http://callmenick.com/_development/slide-push-menus/css/style.min.css">            
         
       
    
  
    
        <link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/base.css"/>
    <!-- *************************************************** -->      
    
    
	    <!--Fix funzionamento scorm su IE9-->
        <meta http-equiv="x-ua-compatible" content="IE=8"></meta>
        <!--END -->

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title><?php echo Layout::title(); ?></title>
		<?php echo Layout::zone('meta'); ?>
		<?php echo Layout::meta(); ?>
		<link rel="shortcut icon" href="<?php echo Layout::path(); ?>images/favicon.png" type="image/png" />
		<link rel="shortcut icon" href="<?php echo Layout::path(); ?>images/favicon.ico" />
		<!-- reset and font stylesheet -->
		<?php echo Layout::resetter(); ?>
		<!-- common stylesheet -->
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/base.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/base-old-treeview.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/lms.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/lms-to-review.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/lms-menu.css" />
	
    
                     
    
        <?php echo Layout::rtl(); ?>
        
		<!-- specific stylesheet -->

		<!-- printer stylesheet-->
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/print.css" media="print" />
		<?php echo Layout::accessibility(); ?>
		<!-- Page Head area -->
		<script type="text/javascript" src="<?php echo Get::rel_path('base'); ?>/lib/js_utils.js"></script>
		<?php echo Layout::zone('page_head'); ?>
        <script type="text/javascript" src="<?php echo Layout::path(); ?>resources/jquery/jquery.min.js"></script>
        <script src="<?php echo Layout::path(); ?>resources/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="<?php echo Layout::path(); ?>resources/chartist/chartist.min.js"></script>
        <script type="text/javascript" src="<?php echo Layout::path(); ?>resources/chartist/chartist-plugin-pointlabels.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>resources/chartist/chartist.min.css" />
        <script>
            $('document').ready(function() {
                $('div.menu-area a').bind('click',function(event){
                	event.preventDefault();
                    id = $(this).attr('rel');
                    $('ul.float-left').hide();
                    $('ul#'+id).show();
                    $('div.menu-area').removeClass('menu-selected');
                    $(this).parent().addClass('menu-selected');
                });

                 $( "#accordion" ).accordion({
                    collapsible: true,
                    active:false,
                    icons:false
                });


            });
        </script>
		<?php echo Layout::rtl(); ?>
        
        
 
  
    
 <!-- HELP DESK -->  
  <link rel="stylesheet" type="text/css" media="all" href="<?php echo Layout::path(); ?>style/helpdesk.css">
  <link rel="stylesheet" type="text/css" media="all" href="<?php echo Layout::path(); ?>style/jquery.fancybox.css">
  <script type="text/javascript" src="<?php echo Layout::path(); ?>bootstrap/js/jquery.min.js"></script>
   <script type="text/javascript" src="<?php echo Layout::path(); ?>bootstrap/js/jquery.fancybox.js?v=2.0.6"></script>     
 
    <link href="<?php echo Layout::path(); ?>bootstrap/css/bootstrap-toggle.min.css" rel="stylesheet">
   <script src="<?php echo Layout::path(); ?>bootstrap/js/bootstrap-toggle.min.js"></script>
       

  <script src="<?php echo Layout::path(); ?>script/helpdesk.js"></script>         
            
        
      <!-- TOOLTIP -->
    <link href="<?php echo Layout::path(); ?>resources/jquery/jquery-ui-1.11.4.min.css" rel="stylesheet">
    <script src="<?php echo Layout::path(); ?>resources/jquery/jquery-ui-1.11.4.min.js"></script>

        
        
	</head>
	<body class="yui-skin-docebo yui-skin-sam">
		<!-- blind nav -->
		<?php echo Layout::zone('blind_navigation'); ?>
		<!-- feedback -->
		<?php echo Layout::zone('feedback'); ?>
		<!-- container -->
		<div id="container">
		<!-- header -->

        
		<!-- menu_over -->
		<?php echo Layout::zone('menu_over'); ?>
                
                
                                            
		<!-- content -->
		<div id="lms_main_container" class="row">
        
        <div id="course-info-boot" class="col-md-3">
				<?php echo Layout::zone('menu'); ?>
			</div>  
		
        	<div id="yui-main-boot" class='col-md-9'>
				<?php
				if(!isset($_SESSION['direct_play']))
					echo '<div class="yui-b">'.Layout::zone('content').'</div>';
				else
					echo Layout::zone('content');
				?>
                
                
			</div>
            
			<div class="nofloat"></div>
		</div>
		<!-- footer -->
		<div id="footer" class="layout_footer">
			<?php echo Layout::zone('footer'); ?>
				<?php echo Layout::copyright(); ?>
		</div>
		</div>
		<!-- scripts -->
		<?php echo Layout::zone('scripts'); ?>
		<!-- debug -->
		<?php echo Layout::zone('debug'); ?>
		<!-- def_lang -->
		<?php echo Layout::zone('def_lang'); ?>
		<?php echo Layout::analytics(); ?>
        
        
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
</html>