var $ = require('jquery');
var Accordion = require('../components/accordion');

var FolderView = (function() {
  $(document).ready(function() {
    var _$folderView = $('.folder-view');
    if (_$folderView.length) {
      return new Accordion(_$folderView[0]);
    }
  });
})();

module.exports = FolderView;
