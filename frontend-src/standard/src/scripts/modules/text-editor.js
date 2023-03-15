var $ = require('jquery');
import TinyMce from '../components/tinymce';

const TextEditor = (function() {
  $(document).ready(function () {
    return new TinyMce();
  });
})();

module.exports = TextEditor;
