var $ = require('jquery');
import TinyMce from '../components/tinymce';

const TextEditor = (function() {
  $(function(){
    return new TinyMce();
  });
})();

export default TextEditor;
