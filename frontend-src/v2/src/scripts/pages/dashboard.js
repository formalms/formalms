//var $ = require('jquery');
import Calendar from '../modules/calendar'

var Page = (function() {
  function Page() {
    new Calendar()
  }

  Page.prototype.setData = function() {};

  Page.prototype.load = function() {};

  return new Page();
})();

module.exports = Page;
