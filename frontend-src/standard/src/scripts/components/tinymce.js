import Config from '../config/config';
import tinymce from 'tinymce/tinymce';

import 'tinymce/icons/default';

import 'tinymce/themes/silver/theme';
import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/ui/oxide/content.css';

import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/print';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/hr';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/pagebreak';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/visualchars';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/media';
import 'tinymce/plugins/nonbreaking';
import 'tinymce/plugins/save';
import 'tinymce/plugins/table';
import 'tinymce/plugins/contextmenu';
import 'tinymce/plugins/directionality';
import 'tinymce/plugins/emoticons';
import 'tinymce/plugins/template';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/textcolor';
import 'tinymce/plugins/colorpicker';
import 'tinymce/plugins/textpattern';
import 'tinymce/plugins/imagetools';

class TinyMce {

  constructor() {
    this.init();
  }

  getPopupUrl(slug, params) {
    let url = `${Config.apiUrl}${slug}`;
    if (!params) {
      params = {};
    }
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }

  init() {
    this.initComplex('tinymce_complex');
    this.initSimple('tinymce_simple');
  }

  initComplex(editor_selector) {
    const obj = this;

    tinymce.init({
      mode: 'textareas',
      skin: false,
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
          url: `${obj.getPopupUrl('adm/mediagallery/show', { type: meta.filetype })}`,
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
    const obj = this;

    tinymce.init({
      mode: 'textareas',
      skin: false,
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
          url: `${obj.getPopupUrl('adm/mediagallery/show', { type: meta.filetype })}`,
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
