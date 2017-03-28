'use strict';

var Page = (function () {

	function Page() {
		console.log('all page constructor');
	}

	Page.prototype.setData = function (data) {
		console.log(data);
	};
	
	Page.prototype.load = function () {
		document.body.classList.add('appIsReady');
		console.log('all page load');
	};

	return new Page();

})();

module.exports = Page;