var $ = require('jquery');
const TinyMce = require('../components/tinymce');

const TextEditor = (function() {
  $(document).ready(function() {
    return new TinyMce();
  });
})();

module.exports = TextEditor;
