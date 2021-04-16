import Config from '../config/config';
const tinymce = require('tinymce');

class TinyMce {

  constructor(options) {
    console.log(options);
    this.init();
  }

  getApiUrl(params) {
    let url = Config.apiUrl;
    if (!params) {
      params = {};
    }
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }

  init() {
    this.initComplex('tinymce_complex');
    this.initComplex('tinymce_simple');
  }

  initComplex(editor_selector) {
    tinymce.init({
      mode: 'textareas',
      theme: 'silver',
      branding: false,
      editor_selector,
      forced_root_block: false,
      force_p_newlines: false,
      force_br_newlines: true,
      relative_urls: true,
      remove_script_host: false,
      removed_menuitems: 'newdocument',
      plugins: [
        'advlist autolink lists link image charmap print preview hr anchor pagebreak',
        'searchreplace wordcount visualblocks visualchars code fullscreen',
        'insertdatetime media nonbreaking save table contextmenu directionality',
        'emoticons template paste textcolor colorpicker textpattern imagetools',
      ],
      toolbar1:
        'insertfile undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | formatselect fontsizeselect',
      toolbar2: 'media | forecolor backcolor emoticons',
      image_advtab: true,
      theme_advanced_buttons1:
        'code,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect',
      theme_advanced_buttons2:
        'cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,cleanup,|,forecolor,backcolor,removeformat,|,charmap,emotions,iespell,media,|,fullscreen,attribs',
      theme_advanced_buttons3: '',
      theme_advanced_toolbar_location: 'top',
      theme_advanced_toolbar_align: 'center',
      height: '250px',
      width: '100%',
      file_picker_callback: function (callback, value, meta) {
        tinymce.activeEditor.windowManager.openUrl({
          title: 'Forma File Browser',
          url: `${this.getApiUrl({ type: meta.filetype })}`,
          width: 800,
          height: 600,
          resizable: true,
          maximizable: true,
          inline: 1,
        });
      },
    });
  }

  initSimple(editor_selector) {
    tinymce.init({
      mode : 'textareas',
      theme : 'silver',
      editor_selector,
      forced_root_block : false,
      force_p_newlines : false,
      force_br_newlines : true,
      relative_urls : true,
      remove_script_host: false,
      plugins: [
        'advlist autolink lists link image charmap print preview hr anchor pagebreak',
        'searchreplace wordcount visualblocks visualchars code fullscreen',
        'insertdatetime media nonbreaking save table contextmenu directionality',
        'emoticons template paste textcolor colorpicker textpattern imagetools'
      ],
      toolbar1: 'bold italic underlined | bullist numlist outdent indent | link image | insertfile | code | upload',
      image_advtab: true,
      theme_advanced_buttons1 : '',
      theme_advanced_buttons2 : '',
      theme_advanced_buttons3 : '',
      theme_advanced_toolbar_location : 'top',
      theme_advanced_toolbar_align : 'center',
      height:'250px',
      width:'100%',
      file_picker_callback: function (callback, value, meta) {
        tinymce.activeEditor.windowManager.openUrl({
          title: 'Forma File Browser',
          url: `${this.getApiUrl({ type: meta.filetype })}`,
          width: 800,
          height: 600,
          resizable: true,
          maximizable: true,
          inline: 1,
        });
      },
    });
  }

}

export default TinyMce
