/*
 * Integration in Docebo for TinyMce editor
 */

tinyMCE.init
({
	theme : "advanced",
	mode : "textareas",
	editor_selector : "tinymce_complex",
	forced_root_block : false,
	force_p_newlines : false,
	force_br_newlines : true,
	relative_urls : false,
	remove_script_host: false,
	plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
	theme_advanced_toolbar_location : "top",
	theme_advanced_buttons1 : "fullscreen,code,newdocument,|"
	+ ",cut,copy,paste,pastetext,pasteword,|"
	+ ",undo,redo,|"
	+ ",bullist,numlist,|"
	+ ",bold,italic,underline,strikethrough,|"
	+ ",justifyleft,justifycenter,justifyright,justifyfull,|"
	+ ",link,unlink",
	theme_advanced_buttons2 : "image,media,emotions,table,|"
	+ ",charmap,|"
	+ ",fontsizeselect,|"
	+ ",forecolor,backcolor",
	theme_advanced_buttons3 : "",
	height:"250px",
	width:"100%",
	file_browser_callback : 'myFileBrowser'
});

tinyMCE.init
({
	theme : "advanced",
	mode : "textareas",
	editor_selector : "tinymce_simple",
	forced_root_block : false,
	force_p_newlines : false,
	force_br_newlines : true,
	relative_urls : false,
	remove_script_host: false,
	plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
	theme_advanced_toolbar_location : "top",
	theme_advanced_buttons1 : "cut,copy,paste,|"
	+ ",undo,redo,|"
	+ ",bold,italic,underline,|"
	+ ",link,unlink",
	theme_advanced_buttons2 : "image,media,|"
	+ ",fontsizeselect,|"
	+ ",forecolor,backcolor,|"
	+ ",code",
	theme_advanced_buttons3 : "",
	height:"250px",
	width:"100%",
	file_browser_callback : 'myFileBrowser'
});

function myFileBrowser (field_name, url, type, win)
{
	tinyMCE.activeEditor.windowManager.open({
        file : '../doceboCore/addons/mod_media/index_tiny.php' + "?type=" + type,
        title : 'File Browser',
        width : 800, 
        height : 600,
        resizable : "yes",
        inline : "yes",
        close_previous : "no"
    }, {
        window : win,
        input : field_name
    });
	
	return false;
}