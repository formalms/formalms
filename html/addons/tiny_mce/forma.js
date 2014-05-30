/*
 * Integration in FormaLMS TinyMce editor
 * http://www.tinymce.com/
 * majorVersion : '3',
 * minorVersion : '5.8',
 * releaseDate : '2012-11-20',
 */


tinyMCE.init({
	// General options
	mode : "textareas",
	theme : "advanced",
	skin : "o2k7",
	skin_variant : "silver",
	editor_selector : "tinymce_complex",
	forced_root_block : false,
	force_p_newlines : false,
	force_br_newlines : true,
	relative_urls : true,
	remove_script_host: false,
	plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,visualblocks",

	// Theme options
	theme_advanced_buttons1 : "code,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,cleanup,|,forecolor,backcolor,removeformat,|,charmap,emotions,iespell,media,|,fullscreen,attribs",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "center",

	// Style formats
	style_formats : [
		{title : 'Bold text', inline : 'b'},
		{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
		{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
		{title : 'Example 1', inline : 'span', classes : 'example1'},
		{title : 'Example 2', inline : 'span', classes : 'example2'},
		{title : 'Table styles'},
		{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
	],


	height:"250px",
	width:"100%",
	file_browser_callback : 'myFileBrowser'
});

/***************
// very old configuration - to be discontinued
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
***********/

function myFileBrowser (field_name, url, type, win)
{
	tinyMCE.activeEditor.windowManager.open({
        file : '../appCore/addons/mod_media/index_tiny.php' + "?type=" + type,
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