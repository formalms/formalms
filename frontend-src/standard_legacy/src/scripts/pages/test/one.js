'use strict';
var $ = require('jquery');

var Page = (function() {
  function Page() {
    console.log('one page constructor');
  }

  Page.prototype.setData = function(data) {
    console.log(data);
  };

  Page.prototype.load = function() {
    console.log('one page load');

    $('h1').text('pagina one!!');
  };

  return new Page();
})();

module.exports = Page;
