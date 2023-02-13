'use strict';
var $ = require('jquery');

var Page = (function() {
  function Page() {
    console.log('home page constructor');
  }

  Page.prototype.setData = function(data) {
    console.log(data);
  };

  Page.prototype.load = function() {
    console.log('home page load');

    $('h1').text('pagina index!!');
  };

  return new Page();
})();

module.exports = Page;
