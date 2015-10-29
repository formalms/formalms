<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Layout::lang_code(); ?>">
	<head>
    
    
    <!-- Javascripts area -->
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>      
    
    <!-- common stylesheet -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">          
    
    
	    <!--Fix funzionamento scorm su IE9-->
        <meta http-equiv="x-ua-compatible" content="IE=8"></meta>
        <!--END -->

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
		<link rel="stylesheet" type="text/css" href="<?php echo Layout::path(); ?>style/table-responsive.css" />
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
		<div id="lms_main_container" >
        
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
			<div class="copyright">
				<?php echo Layout::copyright(); ?>
			</div>
		</div>
		</div>
		<!-- scripts -->
		<?php echo Layout::zone('scripts'); ?>
		<!-- debug -->
		<?php echo Layout::zone('debug'); ?>
		<!-- def_lang -->
		<?php echo Layout::zone('def_lang'); ?>
		<?php echo Layout::analytics(); ?>
	</body>
</html>